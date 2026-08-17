(function () {
    'use strict';

    const config = window.attendancePortal || {};
    const routes = config.routes || {};
    const course = String(config.course || '').toUpperCase();
    const official = { amIn: '08:00', amOut: '12:00', pmIn: '13:00', pmOut: '17:00' };
    const allowedStatuses = ['', 'present', 'absent', 'late', 'half_day', 'leave', 'holiday', 'official_business'];
    const statusLabels = {
        '': 'No entry',
        present: 'Present',
        absent: 'Absent',
        late: 'Late',
        half_day: 'Half day',
        leave: 'Leave',
        holiday: 'Holiday',
        official_business: 'Official business'
    };

    const state = {
        currentDate: startOfCurrentCutoff(new Date()),
        cutoffDates: [],
        employees: [],
        filtered: [],
        selectedKeys: new Set(),
        editingKey: null,
        dialogDirty: false,
        controller: null
    };
    const elements = {};

    document.addEventListener('DOMContentLoaded', init);

    function init() {
        cacheElements();
        bindEvents();
        updateCutoff();
        loadRegister();
    }

    function cacheElements() {
        [
            'previous-cutoff', 'next-cutoff', 'current-cutoff', 'cutoff-label',
            'export-attendance', 'employee-search', 'register-loading', 'register-error',
            'register-error-message', 'retry-register', 'register-empty', 'register-table-wrap',
            'register-body', 'select-all', 'bulk-actions', 'selected-count', 'clear-selection',
            'delete-selected', 'summary-personnel', 'summary-records', 'summary-hours',
            'summary-review', 'dtr-dialog', 'dialog-employee', 'dialog-period', 'entry-body',
            'close-dialog', 'cancel-dialog', 'save-entries', 'dialog-metrics', 'toast-region'
        ].forEach(function (id) {
            elements[toCamel(id)] = document.getElementById(id);
        });
    }

    function bindEvents() {
        elements.previousCutoff.addEventListener('click', previousCutoff);
        elements.nextCutoff.addEventListener('click', nextCutoff);
        elements.currentCutoff.addEventListener('click', goToCurrentCutoff);
        elements.retryRegister.addEventListener('click', loadRegister);
        elements.exportAttendance.addEventListener('click', exportAttendance);
        elements.employeeSearch.addEventListener('input', applySearch);
        elements.selectAll.addEventListener('change', toggleSelectAll);
        elements.clearSelection.addEventListener('click', clearSelection);
        elements.deleteSelected.addEventListener('click', deleteSelected);
        elements.closeDialog.addEventListener('click', closeDialog);
        elements.cancelDialog.addEventListener('click', closeDialog);
        elements.saveEntries.addEventListener('click', saveEntries);
        elements.dtrDialog.addEventListener('click', closeOnBackdrop);
        elements.dtrDialog.addEventListener('cancel', function (event) {
            event.preventDefault();
            closeDialog();
        });
    }

    function toCamel(value) {
        return value.replace(/-([a-z])/g, function (_, letter) { return letter.toUpperCase(); });
    }

    function startOfCurrentCutoff(date) {
        const result = new Date(date.getFullYear(), date.getMonth(), date.getDate() <= 15 ? 1 : 16);
        result.setHours(0, 0, 0, 0);
        return result;
    }

    function cutoffStart(date) {
        return new Date(date.getFullYear(), date.getMonth(), date.getDate() <= 15 ? 1 : 16);
    }

    function cutoffEnd(date) {
        return date.getDate() <= 15
            ? new Date(date.getFullYear(), date.getMonth(), 15)
            : new Date(date.getFullYear(), date.getMonth() + 1, 0);
    }

    function updateCutoff() {
        const start = cutoffStart(state.currentDate);
        const end = cutoffEnd(state.currentDate);
        const formatter = new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric', year: 'numeric' });

        elements.cutoffLabel.textContent = formatter.format(start) + ' - ' + formatter.format(end);
        state.cutoffDates = [];

        const cursor = new Date(start);
        while (cursor <= end) {
            state.cutoffDates.push(formatLocalDate(cursor));
            cursor.setDate(cursor.getDate() + 1);
        }

        const current = startOfCurrentCutoff(new Date());
        elements.currentCutoff.disabled = formatLocalDate(start) === formatLocalDate(current);
    }

    function previousCutoff() {
        const current = cutoffStart(state.currentDate);
        state.currentDate = current.getDate() === 1
            ? new Date(current.getFullYear(), current.getMonth() - 1, 16)
            : new Date(current.getFullYear(), current.getMonth(), 1);
        changeCutoff();
    }

    function nextCutoff() {
        const current = cutoffStart(state.currentDate);
        state.currentDate = current.getDate() === 1
            ? new Date(current.getFullYear(), current.getMonth(), 16)
            : new Date(current.getFullYear(), current.getMonth() + 1, 1);
        changeCutoff();
    }

    function goToCurrentCutoff() {
        state.currentDate = startOfCurrentCutoff(new Date());
        changeCutoff();
    }

    function changeCutoff() {
        updateCutoff();
        clearSelection();
        loadRegister();
    }

    function formatLocalDate(date) {
        return date.getFullYear() + '-'
            + String(date.getMonth() + 1).padStart(2, '0') + '-'
            + String(date.getDate()).padStart(2, '0');
    }

    async function loadRegister() {
        if (!course) {
            showLoadError('This account does not have an assigned department.');
            return;
        }
        if (state.controller) state.controller.abort();
        state.controller = new AbortController();
        setLoadState('loading');

        const start = formatLocalDate(cutoffStart(state.currentDate));
        const url = routes.attendanceData + '/' + encodeURIComponent(course.toLowerCase())
            + '?cutoff_start=' + encodeURIComponent(start);

        try {
            const response = await fetch(url, {
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
                signal: state.controller.signal
            });
            if (response.status === 401) {
                window.location.assign(routes.login);
                return;
            }

            const data = await parseJsonResponse(response);
            if (!response.ok) throw new Error(data.error || data.message || 'Unable to retrieve attendance data.');
            if (!Array.isArray(data)) throw new Error('The attendance service returned an invalid response.');

            state.employees = normalizeEmployees(data);
            state.selectedKeys.clear();
            applySearch();
            updateSummary();
            setLoadState(state.employees.length ? 'ready' : 'empty');
        } catch (error) {
            if (error.name !== 'AbortError') showLoadError(error.message || 'Unable to retrieve attendance data.');
        }
    }

    function normalizeEmployees(data) {
        const seen = new Map();

        data.forEach(function (item) {
            if (!item || !item.id || !item.employee_name) return;
            const rawId = numericId(item.raw_id || item.id);
            if (!rawId) return;

            const employeeType = String(item.employee_type || 'Employee').trim();
            const key = rawId + '|' + employeeType.toUpperCase();
            const attendance = {};

            state.cutoffDates.forEach(function (date) {
                const saved = item.saved_times && item.saved_times[date] ? item.saved_times[date] : {};
                attendance[date] = {
                    am_in: cleanTime(saved.am_in),
                    am_out: cleanTime(saved.am_out),
                    pm_in: cleanTime(saved.pm_in),
                    pm_out: cleanTime(saved.pm_out),
                    status: allowedStatuses.includes(saved.status) ? saved.status : '',
                    remarks: String(saved.remarks || '')
                };
            });

            const normalized = {
                key: key,
                id: rawId,
                displayId: String(item.id),
                name: String(item.employee_name).trim(),
                email: String(item.email || '').trim(),
                designation: String(item.designation || '').trim(),
                type: employeeType,
                attendance: attendance
            };

            if (!seen.has(key)) {
                seen.set(key, normalized);
                return;
            }

            const existing = seen.get(key);
            state.cutoffDates.forEach(function (date) {
                if (!hasRecord(existing.attendance[date]) && hasRecord(normalized.attendance[date])) {
                    existing.attendance[date] = normalized.attendance[date];
                }
            });
        });

        return Array.from(seen.values()).sort(function (a, b) {
            return a.name.localeCompare(b.name, undefined, { sensitivity: 'base' });
        });
    }

    function cleanTime(value) {
        const match = String(value || '').match(/^(\d{2}):(\d{2})/);
        return match ? match[1] + ':' + match[2] : '';
    }

    function numericId(value) {
        const matches = String(value == null ? '' : value).match(/\d+/g);
        return matches ? (Number(matches.join('')) || 0) : 0;
    }

    function applySearch() {
        const query = elements.employeeSearch.value.trim().toLocaleLowerCase();
        state.filtered = state.employees.filter(function (employee) {
            if (!query) return true;
            return [employee.name, employee.designation, employee.type, employee.email, String(employee.id)]
                .some(function (value) { return value.toLocaleLowerCase().includes(query); });
        });
        renderRegister();
    }

    function renderRegister() {
        elements.registerBody.textContent = '';
        state.filtered.forEach(function (employee) {
            elements.registerBody.appendChild(buildRegisterRow(employee));
        });

        if (state.employees.length && !state.filtered.length) {
            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = 9;
            cell.className = 'search-empty-cell';
            cell.textContent = 'No personnel match your search.';
            row.appendChild(cell);
            elements.registerBody.appendChild(row);
        }
        updateSelection();
    }

    function buildRegisterRow(employee) {
        const summary = summarizeEmployee(employee);
        const row = document.createElement('tr');
        row.dataset.employeeKey = employee.key;
        row.classList.toggle('is-selected', state.selectedKeys.has(employee.key));

        const checkCell = createCell('cell-check');
        const checkbox = document.createElement('input');
        checkbox.type = 'checkbox';
        checkbox.className = 'employee-checkbox';
        checkbox.checked = state.selectedKeys.has(employee.key);
        checkbox.setAttribute('aria-label', 'Select ' + employee.name);
        checkbox.addEventListener('change', function () {
            if (checkbox.checked) state.selectedKeys.add(employee.key);
            else state.selectedKeys.delete(employee.key);
            row.classList.toggle('is-selected', checkbox.checked);
            updateSelection();
        });
        checkCell.appendChild(checkbox);
        row.appendChild(checkCell);

        const employeeCell = createCell('cell-employee');
        const name = document.createElement('div');
        name.className = 'employee-name';
        name.textContent = employee.name;
        const meta = document.createElement('div');
        meta.className = 'employee-meta';
        meta.textContent = [employee.designation, employee.email || ('Employee ID ' + employee.id)].filter(Boolean).join(' | ');
        employeeCell.append(name, meta);
        row.appendChild(employeeCell);

        const typeCell = createCell('cell-type');
        const type = document.createElement('span');
        type.className = 'type-label';
        type.textContent = employee.type;
        typeCell.appendChild(type);
        row.appendChild(typeCell);

        row.appendChild(numberCell(String(summary.days)));
        row.appendChild(numberCell((summary.worked / 60).toFixed(2)));
        row.appendChild(numberCell(summary.lateness + ' min'));
        row.appendChild(numberCell(summary.undertime + ' min'));

        const statusCell = createCell('cell-status');
        const badge = document.createElement('span');
        const status = summary.records === 0 ? 'empty' : (summary.review ? 'review' : 'ready');
        badge.className = 'status-badge status-badge--' + status;
        badge.textContent = status === 'empty' ? 'No entries' : (status === 'review' ? 'Needs review' : 'Recorded');
        statusCell.appendChild(badge);
        row.appendChild(statusCell);

        const actionsCell = createCell('cell-actions');
        const actions = document.createElement('div');
        actions.className = 'row-actions';
        actions.appendChild(actionButton('bi-pencil-square', 'Edit cutoff entries', function () { openEditor(employee.key); }));
        actions.appendChild(actionLink('bi-file-earmark-text', 'Open monthly DTR', dtrUrl(employee, false)));
        actions.appendChild(actionLink('bi-printer', 'Print monthly DTR', dtrUrl(employee, true), true));
        actionsCell.appendChild(actions);
        row.appendChild(actionsCell);
        return row;
    }

    function createCell(className) {
        const cell = document.createElement('td');
        if (className) cell.className = className;
        return cell;
    }

    function numberCell(text) {
        const cell = createCell('cell-number');
        cell.textContent = text;
        return cell;
    }

    function actionButton(icon, label, handler) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'row-action row-action--edit';
        button.title = label;
        button.setAttribute('aria-label', label);
        button.innerHTML = '<i class="bi ' + icon + '" aria-hidden="true"></i>';
        button.addEventListener('click', handler);
        return button;
    }

    function actionLink(icon, label, href, newTab) {
        const link = document.createElement('a');
        link.className = 'row-action';
        link.href = href;
        link.title = label;
        link.setAttribute('aria-label', label);
        link.innerHTML = '<i class="bi ' + icon + '" aria-hidden="true"></i>';
        if (newTab) {
            link.target = '_blank';
            link.rel = 'noopener';
        }
        return link;
    }

    function dtrUrl(employee, print) {
        const month = formatLocalDate(cutoffStart(state.currentDate)).slice(0, 7);
        return routes.dtrBase + '/' + encodeURIComponent(course) + '/' + employee.id
            + (print ? '/print' : '') + '?month=' + encodeURIComponent(month)
            + '&type=' + encodeURIComponent(employee.type);
    }

    function summarizeEmployee(employee) {
        const summary = { days: 0, records: 0, worked: 0, lateness: 0, undertime: 0, review: 0 };
        state.cutoffDates.forEach(function (date) {
            const entry = employee.attendance[date] || emptyEntry();
            if (!hasRecord(entry)) return;
            const metrics = calculateMetrics(entry);
            summary.records += 1;
            if (['present', 'late', 'half_day'].includes(entry.status) || hasTimes(entry)) summary.days += 1;
            summary.worked += metrics.worked;
            summary.lateness += metrics.lateness;
            summary.undertime += metrics.undertime;
            if (metrics.incomplete) summary.review += 1;
        });
        return summary;
    }

    function updateSummary() {
        let records = 0;
        let worked = 0;
        let review = 0;
        state.employees.forEach(function (employee) {
            const summary = summarizeEmployee(employee);
            records += summary.records;
            worked += summary.worked;
            review += summary.review;
        });
        elements.summaryPersonnel.textContent = String(state.employees.length);
        elements.summaryRecords.textContent = String(records);
        elements.summaryHours.textContent = (worked / 60).toFixed(2);
        elements.summaryReview.textContent = String(review);
    }

    function calculateMetrics(entry) {
        const count = ['am_in', 'am_out', 'pm_in', 'pm_out'].filter(function (key) { return Boolean(entry[key]); }).length;
        const worked = timeSpan(entry.am_in, entry.am_out) + timeSpan(entry.pm_in, entry.pm_out);
        const lateness = Math.max(0, toMinutes(entry.am_in) - toMinutes(official.amIn))
            + Math.max(0, toMinutes(entry.pm_in) - toMinutes(official.pmIn));
        const scheduledUndertime = lateness
            + minutesBefore(entry.am_out, official.amOut)
            + minutesBefore(entry.pm_out, official.pmOut);
        const isPresent = ['present', 'late', 'half_day'].includes(entry.status) || count > 0;
        const invalidOrder = invalidPair(entry.am_in, entry.am_out)
            || invalidPair(entry.pm_in, entry.pm_out);
        const completeHalfDay = validPair(entry.am_in, entry.am_out)
            || validPair(entry.pm_in, entry.pm_out);
        return {
            worked: worked,
            lateness: isPresent ? lateness : 0,
            undertime: isPresent ? Math.max(0, 480 - worked, scheduledUndertime) : 0,
            overtime: isPresent
                ? Math.max(0, toMinutes(entry.am_out) - toMinutes(official.amOut))
                    + Math.max(0, toMinutes(entry.pm_out) - toMinutes(official.pmOut))
                : 0,
            incomplete: isPresent && (entry.status === 'half_day'
                ? !completeHalfDay
                : count < 4 || invalidOrder)
        };
    }

    function invalidPair(from, to) {
        return Boolean(from && to && toMinutes(to) <= toMinutes(from));
    }

    function validPair(from, to) {
        return Boolean(from && to && toMinutes(to) > toMinutes(from));
    }

    function minutesBefore(value, scheduled) {
        return value ? Math.max(0, toMinutes(scheduled) - toMinutes(value)) : 0;
    }

    function timeSpan(from, to) {
        if (!from || !to) return 0;
        const start = toMinutes(from);
        const end = toMinutes(to);
        return end > start ? end - start : 0;
    }

    function toMinutes(value) {
        if (!value || !/^\d{2}:\d{2}$/.test(value)) return 0;
        const parts = value.split(':').map(Number);
        return parts[0] * 60 + parts[1];
    }

    function hasTimes(entry) {
        return Boolean(entry.am_in || entry.am_out || entry.pm_in || entry.pm_out);
    }

    function hasRecord(entry) {
        return hasTimes(entry) || Boolean(entry.status || entry.remarks);
    }

    function emptyEntry() {
        return { am_in: '', am_out: '', pm_in: '', pm_out: '', status: '', remarks: '' };
    }

    function openEditor(key) {
        const employee = state.employees.find(function (item) { return item.key === key; });
        if (!employee) return;
        state.editingKey = key;
        elements.dialogEmployee.textContent = employee.name;
        elements.dialogPeriod.textContent = elements.cutoffLabel.textContent;
        renderEntryRows(employee);
        state.dialogDirty = false;
        elements.dtrDialog.showModal();
    }

    function renderEntryRows(employee) {
        elements.entryBody.textContent = '';
        const today = new Date();
        today.setHours(23, 59, 59, 999);

        state.cutoffDates.forEach(function (date) {
            const entry = employee.attendance[date] || emptyEntry();
            const parsedDate = parseLocalDate(date);
            const isFuture = parsedDate > today;
            const row = document.createElement('tr');
            row.dataset.date = date;
            row.classList.toggle('is-weekend', parsedDate.getDay() === 0 || parsedDate.getDay() === 6);

            const dateCell = document.createElement('td');
            dateCell.className = 'entry-date';
            dateCell.textContent = new Intl.DateTimeFormat('en-US', { month: 'short', day: 'numeric' }).format(parsedDate);
            const weekday = document.createElement('small');
            weekday.textContent = new Intl.DateTimeFormat('en-US', { weekday: 'long' }).format(parsedDate);
            dateCell.appendChild(weekday);
            row.appendChild(dateCell);

            const statusCell = document.createElement('td');
            const status = document.createElement('select');
            status.dataset.field = 'status';
            status.disabled = isFuture;
            status.setAttribute('aria-label', 'Status for ' + date);
            allowedStatuses.forEach(function (value) {
                const option = document.createElement('option');
                option.value = value;
                option.textContent = statusLabels[value];
                option.selected = entry.status === value;
                status.appendChild(option);
            });
            status.addEventListener('change', function () {
                state.dialogDirty = true;
                setRowMode(row);
                updateEntryRow(row);
                updateDialogMetrics();
            });
            statusCell.appendChild(status);
            row.appendChild(statusCell);

            const remarksCell = document.createElement('td');
            const remarks = document.createElement('input');
            remarks.type = 'text';
            remarks.maxLength = 1000;
            remarks.value = entry.remarks || '';
            remarks.dataset.field = 'remarks';
            remarks.disabled = isFuture;
            remarks.setAttribute('aria-label', 'Remarks for ' + date);
            remarks.addEventListener('input', function () {
                state.dialogDirty = true;
                updateDialogMetrics();
            });
            remarksCell.appendChild(remarks);
            row.appendChild(remarksCell);

            ['am_in', 'am_out', 'pm_in', 'pm_out'].forEach(function (field) {
                const cell = document.createElement('td');
                const input = document.createElement('input');
                input.type = 'time';
                input.step = '60';
                input.value = entry[field] || '';
                input.dataset.field = field;
                input.dataset.future = isFuture ? '1' : '0';
                input.setAttribute('aria-label', field.replace('_', ' ') + ' for ' + date);
                input.addEventListener('input', function () {
                    state.dialogDirty = true;
                    if (input.value && !status.value) status.value = 'present';
                    updateEntryRow(row);
                    updateDialogMetrics();
                });
                cell.appendChild(input);
                row.appendChild(cell);
            });

            ['worked', 'undertime'].forEach(function (metric) {
                const cell = document.createElement('td');
                cell.className = 'entry-metric';
                cell.dataset.metric = metric;
                row.appendChild(cell);
            });

            const clearCell = document.createElement('td');
            const clearButton = document.createElement('button');
            clearButton.type = 'button';
            clearButton.className = 'entry-clear';
            clearButton.disabled = isFuture;
            clearButton.title = 'Clear day';
            clearButton.setAttribute('aria-label', 'Clear entries for ' + date);
            clearButton.innerHTML = '<i class="bi bi-x-circle" aria-hidden="true"></i>';
            clearButton.addEventListener('click', function () {
                state.dialogDirty = true;
                row.querySelectorAll('input[type="time"]').forEach(function (input) { input.value = ''; });
                remarks.value = '';
                status.value = '';
                setRowMode(row);
                updateEntryRow(row);
                updateDialogMetrics();
            });
            clearCell.appendChild(clearButton);
            row.appendChild(clearCell);

            elements.entryBody.appendChild(row);
            setRowMode(row);
            updateEntryRow(row);
        });
        updateDialogMetrics();
    }

    function setRowMode(row) {
        const status = row.querySelector('[data-field="status"]').value;
        const disablesTimes = ['absent', 'leave', 'holiday', 'official_business'].includes(status);
        row.querySelectorAll('input[type="time"]').forEach(function (input) {
            if (disablesTimes) input.value = '';
            input.disabled = disablesTimes || input.dataset.future === '1';
        });
    }

    function entryFromRow(row) {
        const entry = emptyEntry();
        row.querySelectorAll('[data-field]').forEach(function (field) {
            entry[field.dataset.field] = field.value;
        });
        return entry;
    }

    function updateEntryRow(row) {
        const metrics = calculateMetrics(entryFromRow(row));
        const worked = row.querySelector('[data-metric="worked"]');
        const undertime = row.querySelector('[data-metric="undertime"]');
        worked.textContent = metrics.worked ? formatMinutes(metrics.worked) : '-';
        undertime.textContent = metrics.undertime ? formatMinutes(metrics.undertime) : '-';
        undertime.classList.toggle('is-warning', metrics.undertime > 0);
        row.classList.toggle('has-incomplete', metrics.incomplete);
    }

    function updateDialogMetrics() {
        let records = 0;
        let worked = 0;
        let review = 0;
        elements.entryBody.querySelectorAll('tr').forEach(function (row) {
            const entry = entryFromRow(row);
            const metrics = calculateMetrics(entry);
            if (hasRecord(entry)) records += 1;
            worked += metrics.worked;
            if (metrics.incomplete) review += 1;
        });
        elements.dialogMetrics.textContent = records + ' record' + (records === 1 ? '' : 's')
            + ' | ' + (worked / 60).toFixed(2) + ' hours'
            + (review ? ' | ' + review + ' incomplete' : '');
    }

    function formatMinutes(minutes) {
        const hours = Math.floor(minutes / 60);
        const mins = minutes % 60;
        if (hours && mins) return hours + 'h ' + mins + 'm';
        if (hours) return hours + 'h';
        return mins + 'm';
    }

    function parseLocalDate(value) {
        const parts = value.split('-').map(Number);
        return new Date(parts[0], parts[1] - 1, parts[2]);
    }

    function closeDialog(force) {
        if (force !== true && state.dialogDirty
            && !window.confirm('Discard the unsaved daily time entries?')) {
            return;
        }
        if (elements.dtrDialog.open) elements.dtrDialog.close();
        state.editingKey = null;
        state.dialogDirty = false;
    }

    function closeOnBackdrop(event) {
        if (event.target !== elements.dtrDialog) return;
        const rect = elements.dtrDialog.getBoundingClientRect();
        const inside = event.clientX >= rect.left && event.clientX <= rect.right
            && event.clientY >= rect.top && event.clientY <= rect.bottom;
        if (!inside) closeDialog();
    }

    async function saveEntries() {
        const employee = state.employees.find(function (item) { return item.key === state.editingKey; });
        if (!employee) return;

        const attendance = {};
        let invalidRow = null;
        elements.entryBody.querySelectorAll('tr').forEach(function (row) {
            const entry = entryFromRow(row);
            attendance[row.dataset.date] = entry;
            if (!invalidRow && entry.remarks && !entry.status && !hasTimes(entry)) {
                invalidRow = row;
            }
        });
        if (invalidRow) {
            showToast('Choose a status for each day that has remarks.', 'warning');
            invalidRow.querySelector('[data-field="status"]').focus();
            return;
        }
        const payload = buildPayload(employee, attendance);
        setButtonBusy(elements.saveEntries, true, 'Saving');

        try {
            const response = await postJson(routes.saveAttendance, payload);
            const result = await parseJsonResponse(response);
            if (response.status === 401) {
                window.location.assign(routes.login);
                return;
            }
            if (!response.ok || result.success === false) {
                throw new Error(result.message || 'The daily time entries could not be saved.');
            }
            closeDialog(true);
            showToast(result.message || 'Daily time entries saved.', 'success');
            await loadRegister();
        } catch (error) {
            showToast(error.message || 'The daily time entries could not be saved.', 'error');
        } finally {
            setButtonBusy(elements.saveEntries, false);
        }
    }

    function buildPayload(employee, attendance) {
        return {
            course: course,
            cutoff_start: formatLocalDate(cutoffStart(state.currentDate)),
            attendance_data: [{
                id: employee.id,
                employee_name: employee.name,
                name: employee.name,
                email: employee.email,
                designation: employee.designation,
                employee_type: employee.type,
                type: employee.type,
                attendance: attendance
            }]
        };
    }

    function postJson(url, payload) {
        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        return fetch(url, {
            method: 'POST',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token },
            credentials: 'same-origin',
            body: JSON.stringify(payload)
        });
    }

    async function parseJsonResponse(response) {
        const contentType = response.headers.get('content-type') || '';
        if (!contentType.includes('application/json')) {
            if (response.redirected && response.url.includes('/attendance/attendlog')) {
                window.location.assign(routes.login);
                return {};
            }
            throw new Error('The server returned an unexpected response.');
        }
        return response.json();
    }

    function setButtonBusy(button, busy, label) {
        if (busy) {
            button.dataset.originalHtml = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="bi bi-arrow-repeat busy-icon" aria-hidden="true"></i>' + label;
        } else {
            button.disabled = false;
            if (button.dataset.originalHtml) button.innerHTML = button.dataset.originalHtml;
        }
    }

    function toggleSelectAll() {
        state.filtered.forEach(function (employee) {
            if (elements.selectAll.checked) state.selectedKeys.add(employee.key);
            else state.selectedKeys.delete(employee.key);
        });
        renderRegister();
    }

    function updateSelection() {
        const visibleKeys = state.filtered.map(function (employee) { return employee.key; });
        const visibleSelected = visibleKeys.filter(function (key) { return state.selectedKeys.has(key); }).length;
        elements.selectAll.checked = visibleKeys.length > 0 && visibleSelected === visibleKeys.length;
        elements.selectAll.indeterminate = visibleSelected > 0 && visibleSelected < visibleKeys.length;
        elements.selectedCount.textContent = String(state.selectedKeys.size);
        elements.bulkActions.hidden = state.selectedKeys.size === 0;
    }

    function clearSelection() {
        state.selectedKeys.clear();
        if (elements.selectAll) {
            elements.selectAll.checked = false;
            elements.selectAll.indeterminate = false;
        }
        if (elements.registerBody) renderRegister();
    }

    async function deleteSelected() {
        if (!state.selectedKeys.size) return;
        const selected = state.employees.filter(function (employee) { return state.selectedKeys.has(employee.key); });
        const confirmed = window.confirm(
            'Delete attendance for ' + selected.length + ' selected personnel in ' + elements.cutoffLabel.textContent
            + '?\n\nRecords outside this cutoff will be kept.'
        );
        if (!confirmed) return;

        const payload = {
            course: course,
            cutoff_start: formatLocalDate(cutoffStart(state.currentDate)),
            cutoff_end: formatLocalDate(cutoffEnd(state.currentDate)),
            employees: selected.map(function (employee) { return { id: employee.id, type: employee.type }; }),
            employee_ids: selected.map(function (employee) { return employee.id; })
        };
        setButtonBusy(elements.deleteSelected, true, 'Deleting');

        try {
            const response = await postJson(routes.bulkDelete, payload);
            const result = await parseJsonResponse(response);
            if (response.status === 401) {
                window.location.assign(routes.login);
                return;
            }
            if (!response.ok || result.success === false) {
                throw new Error(result.message || result.error || 'Attendance could not be deleted.');
            }
            showToast(result.message || 'Attendance records deleted.', 'success');
            clearSelection();
            await loadRegister();
        } catch (error) {
            showToast(error.message || 'Attendance could not be deleted.', 'error');
        } finally {
            setButtonBusy(elements.deleteSelected, false);
        }
    }

    function exportAttendance() {
        if (!state.employees.length) {
            showToast('There is no attendance data to export.', 'warning');
            return;
        }

        const rows = [[
            'Employee ID', 'Employee name', 'Designation', 'Employment type', 'Date', 'Status', 'Remarks',
            'AM arrival', 'AM departure', 'PM arrival', 'PM departure', 'Hours worked',
            'Lateness (minutes)', 'Undertime (minutes)', 'Overtime (minutes)'
        ]];
        state.employees.forEach(function (employee) {
            state.cutoffDates.forEach(function (date) {
                const entry = employee.attendance[date] || emptyEntry();
                const metrics = calculateMetrics(entry);
                rows.push([
                    employee.id, employee.name, employee.designation, employee.type, date,
                    entry.status || (hasTimes(entry) ? 'present' : ''), entry.remarks, entry.am_in, entry.am_out,
                    entry.pm_in, entry.pm_out, (metrics.worked / 60).toFixed(2), metrics.lateness,
                    metrics.undertime, metrics.overtime
                ]);
            });
        });

        const csv = rows.map(function (row) {
            return row.map(function (value) {
                return '"' + String(value == null ? '' : value).replace(/"/g, '""') + '"';
            }).join(',');
        }).join('\r\n');
        const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8' });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = 'attendance_' + course.toLowerCase() + '_' + formatLocalDate(cutoffStart(state.currentDate)) + '.csv';
        document.body.appendChild(link);
        link.click();
        link.remove();
        URL.revokeObjectURL(url);
        showToast('CSV export prepared.', 'success');
    }

    function setLoadState(value) {
        elements.registerLoading.hidden = value !== 'loading';
        elements.registerError.hidden = value !== 'error';
        elements.registerEmpty.hidden = value !== 'empty';
        elements.registerTableWrap.hidden = value !== 'ready';
    }

    function showLoadError(message) {
        elements.registerErrorMessage.textContent = message;
        setLoadState('error');
    }

    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = 'toast toast--' + type;
        toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
        const icon = document.createElement('i');
        icon.className = 'bi ' + (type === 'success'
            ? 'bi-check-circle-fill'
            : (type === 'warning' ? 'bi-exclamation-triangle-fill' : 'bi-exclamation-circle-fill'));
        icon.setAttribute('aria-hidden', 'true');
        const text = document.createElement('span');
        text.textContent = message;
        toast.append(icon, text);
        elements.toastRegion.appendChild(toast);
        window.setTimeout(function () { toast.remove(); }, 4800);
    }
})();
