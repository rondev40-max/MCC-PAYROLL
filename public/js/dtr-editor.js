(function () {
    'use strict';

    const specialStatuses = ['absent', 'leave', 'holiday', 'official_business'];
    let dirty = false;

    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('dtr-form');
        const body = document.getElementById('csc-entry-body');
        const month = document.getElementById('month');
        if (!form || !body) return;

        body.querySelectorAll('tr').forEach(bindRow);
        updateTotals();

        form.addEventListener('input', function () { dirty = true; });
        form.addEventListener('change', function () { dirty = true; });
        form.addEventListener('submit', function (event) {
            const invalidRow = Array.from(body.querySelectorAll('tr')).find(function (row) {
                const value = entry(row);
                return value.remarks && !value.status
                    && !(value.am_in || value.am_out || value.pm_in || value.pm_out);
            });

            if (invalidRow) {
                event.preventDefault();
                window.alert('Choose a status for each day that has remarks.');
                invalidRow.querySelector('[data-field="status"]').focus();
                return;
            }

            dirty = false;
        });

        if (month) {
            month.addEventListener('change', function () {
                if (!dirty || window.confirm('Change month and discard unsaved edits?')) {
                    dirty = false;
                    document.getElementById('dtr-period-form').submit();
                } else {
                    month.value = document.querySelector('input[name="month"]').value;
                }
            });
        }

        window.addEventListener('beforeunload', function (event) {
            if (!dirty) return;
            event.preventDefault();
            event.returnValue = '';
        });
    });

    function bindRow(row) {
        const status = row.querySelector('[data-field="status"]');
        const clear = row.querySelector('.csc-row-clear');

        row.querySelectorAll('input[type="time"]').forEach(function (input) {
            input.addEventListener('input', function () {
                if (input.value && status && !status.value) status.value = 'present';
                updateRow(row);
                updateTotals();
            });
        });

        if (status) {
            status.addEventListener('change', function () {
                setRowMode(row, true);
                updateRow(row);
                updateTotals();
            });
        }

        if (clear) {
            clear.addEventListener('click', function () {
                row.querySelectorAll('input').forEach(function (input) { input.value = ''; });
                if (status) status.value = '';
                setRowMode(row, false);
                updateRow(row);
                updateTotals();
                dirty = true;
            });
        }

        setRowMode(row, false);
        updateRow(row);
    }

    function setRowMode(row, clearTimes) {
        const status = row.querySelector('[data-field="status"]');
        const special = status && specialStatuses.includes(status.value);
        const future = row.dataset.future === '1';

        row.querySelectorAll('input[type="time"]').forEach(function (input) {
            if (special && clearTimes) input.value = '';
            input.disabled = special || future;
        });
    }

    function entry(row) {
        const result = { am_in: '', am_out: '', pm_in: '', pm_out: '', status: '' };
        row.querySelectorAll('[data-field]').forEach(function (field) {
            result[field.dataset.field] = field.value;
        });
        return result;
    }

    function metrics(value) {
        const keys = ['am_in', 'am_out', 'pm_in', 'pm_out'];
        const count = keys.filter(function (key) { return Boolean(value[key]); }).length;
        const worked = span(value.am_in, value.am_out) + span(value.pm_in, value.pm_out);
        const present = ['present', 'late', 'half_day'].includes(value.status) || count > 0;
        const invalidOrder = invalidPair(value.am_in, value.am_out)
            || invalidPair(value.pm_in, value.pm_out);
        const completeHalfDay = validPair(value.am_in, value.am_out)
            || validPair(value.pm_in, value.pm_out);
        const scheduledUndertime = minutesAfter(value.am_in, '08:00')
            + minutesBefore(value.am_out, '12:00')
            + minutesAfter(value.pm_in, '13:00')
            + minutesBefore(value.pm_out, '17:00');

        return {
            worked: worked,
            undertime: present ? Math.max(0, 480 - worked, scheduledUndertime) : 0,
            present: present,
            incomplete: present && (value.status === 'half_day'
                ? !completeHalfDay
                : count < 4 || invalidOrder)
        };
    }

    function invalidPair(from, to) {
        return Boolean(from && to && minutes(to) <= minutes(from));
    }

    function validPair(from, to) {
        return Boolean(from && to && minutes(to) > minutes(from));
    }

    function minutesAfter(value, scheduled) {
        return value ? Math.max(0, minutes(value) - minutes(scheduled)) : 0;
    }

    function minutesBefore(value, scheduled) {
        return value ? Math.max(0, minutes(scheduled) - minutes(value)) : 0;
    }

    function span(from, to) {
        if (!from || !to) return 0;
        const start = minutes(from);
        const end = minutes(to);
        return end > start ? end - start : 0;
    }

    function minutes(value) {
        if (!/^\d{2}:\d{2}$/.test(value || '')) return 0;
        const parts = value.split(':').map(Number);
        return parts[0] * 60 + parts[1];
    }

    function updateRow(row) {
        const result = metrics(entry(row));
        const hours = row.querySelector('[data-metric="hours"]');
        const mins = row.querySelector('[data-metric="minutes"]');
        hours.textContent = result.undertime ? Math.floor(result.undertime / 60) : '';
        mins.textContent = result.undertime ? result.undertime % 60 : '';
        hours.classList.toggle('is-warning', result.undertime > 0);
        mins.classList.toggle('is-warning', result.undertime > 0);
        row.classList.toggle('has-incomplete', result.incomplete);
    }

    function updateTotals() {
        let totalUndertime = 0;
        let totalWorked = 0;
        let totalDays = 0;
        let incomplete = 0;

        document.querySelectorAll('#csc-entry-body tr').forEach(function (row) {
            const result = metrics(entry(row));
            totalUndertime += result.undertime;
            totalWorked += result.worked;
            if (result.present) totalDays += 1;
            if (result.incomplete) incomplete += 1;
        });

        document.getElementById('csc-total-hours').textContent = Math.floor(totalUndertime / 60);
        document.getElementById('csc-total-minutes').textContent = totalUndertime % 60;
        document.getElementById('dtr-total-days').textContent = String(totalDays);
        document.getElementById('dtr-total-worked').textContent = humanMinutes(totalWorked);
        document.getElementById('dtr-total-undertime').textContent = humanMinutes(totalUndertime);
        document.getElementById('dtr-incomplete').textContent = String(incomplete);
    }

    function humanMinutes(value) {
        return Math.floor(value / 60) + 'h ' + (value % 60) + 'm';
    }
})();
