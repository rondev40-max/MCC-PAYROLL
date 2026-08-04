<?php
$content = file_get_contents('resources/views/admin/dashboard.blade.php');
if (preg_match('/(.*?)<div class="page-header fu">/s', $content, $matches)) {
    $header = $matches[1];
} else {
    die("Could not extract layout.");
}

$html = $header . '
      <div class="page-header fu">
        <div>
          <div class="page-title">Tax & Gov\'t Deductions</div>
          <div class="page-subtitle">Manage and apply government deductions for employees</div>
        </div>
      </div>

      <!-- Settings Card -->
      <div class="stat-card fu d1 mb-3">
        <h6 class="mb-3"><i class="bi bi-gear" style="color:var(--brand);margin-right:5px;"></i> Deduction Settings</h6>
        <form action="{{ route(\'admin.deductions.update-settings\') }}" method="POST">
          @csrf
          <div class="table-responsive">
            <table class="table table-sm" style="font-size:0.8rem;">
              <thead style="background:var(--brand);color:white;">
                <tr>
                  <th>Deduction Type</th>
                  <th>Rate Type</th>
                  <th>Value</th>
                  <th>Min Amount</th>
                  <th>Max Amount</th>
                  <th>Active</th>
                </tr>
              </thead>
              <tbody>
                @foreach([
                  \'withholding_tax\' => \'Withholding Tax\',
                  \'gsis\' => \'GSIS\',
                  \'philhealth\' => \'PhilHealth\',
                  \'pag_ibig\' => \'Pag-IBIG\',
                  \'sss\' => \'SSS\'
                ] as $key => $label)
                @php $setting = $settings[$key] ?? null; @endphp
                <tr>
                  <td class="align-middle fw-bold">
                    {{ $label }}
                    <input type="hidden" name="deduction_type[]" value="{{ $key }}">
                  </td>
                  <td>
                    <select name="rate_type[]" class="form-select form-select-sm">
                      <option value="percentage" {{ ($setting->rate_type ?? \'percentage\') == \'percentage\' ? \'selected\' : \'\' }}>Percentage (%)</option>
                      <option value="fixed" {{ ($setting->rate_type ?? \'\') == \'fixed\' ? \'selected\' : \'\' }}>Fixed Amount (₱)</option>
                    </select>
                  </td>
                  <td>
                    <input type="number" step="0.01" name="rate_value[]" class="form-control form-control-sm" value="{{ $setting->rate_value ?? 0 }}">
                  </td>
                  <td>
                    <input type="number" step="0.01" name="min_amount[]" class="form-control form-control-sm" value="{{ $setting->min_amount ?? \'\' }}">
                  </td>
                  <td>
                    <input type="number" step="0.01" name="max_amount[]" class="form-control form-control-sm" value="{{ $setting->max_amount ?? \'\' }}">
                  </td>
                  <td class="align-middle text-center">
                    <input class="form-check-input" type="checkbox" name="is_active[{{ $loop->index }}]" value="1" {{ ($setting->is_active ?? 1) ? \'checked\' : \'\' }}>
                  </td>
                </tr>
                @endforeach
              </tbody>
            </table>
          </div>
          <button type="submit" class="btn btn-sm btn-primary mt-2" style="background:var(--brand);"><i class="bi bi-save"></i> Save Settings</button>
        </form>
      </div>

      <!-- Employees List -->
      <div class="stat-card fu d2">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
          <h6 class="m-0"><i class="bi bi-people" style="color:var(--brand);margin-right:5px;"></i> Compute Deductions for Period</h6>
          
          <form method="GET" action="{{ route(\'admin.deductions.index\') }}" class="d-flex gap-2">
            <select name="month" class="form-select form-select-sm" style="width:120px;">
              @foreach($months as $val => $label)
                <option value="{{ $val }}" {{ (int)$month === $val ? \'selected\' : \'\' }}>{{ $label }}</option>
              @endforeach
            </select>
            <select name="year" class="form-select form-select-sm" style="width:100px;">
              @foreach($years as $y)
                <option value="{{ $y }}" {{ (int)$year === $y ? \'selected\' : \'\' }}>{{ $y }}</option>
              @endforeach
            </select>
            <select name="period" class="form-select form-select-sm" style="width:120px;">
              <option value="auto" {{ $period == \'auto\' ? \'selected\' : \'\' }}>Auto Period</option>
              <option value="1-15" {{ $period == \'1-15\' ? \'selected\' : \'\' }}>1-15</option>
              <option value="16-end" {{ $period == \'16-end\' ? \'selected\' : \'\' }}>16-end</option>
              <option value="all" {{ $period == \'all\' ? \'selected\' : \'\' }}>All</option>
            </select>
            <button type="submit" class="btn btn-sm btn-outline-secondary">View</button>
          </form>
          
          <form method="POST" action="{{ route(\'admin.deductions.apply\') }}" class="d-inline">
            @csrf
            <input type="hidden" name="month" value="{{ $month }}">
            <input type="hidden" name="year" value="{{ $year }}">
            <input type="hidden" name="period" value="{{ $period }}">
            <button type="submit" class="btn btn-sm btn-success" style="background:var(--accent);border:none;"><i class="bi bi-check2-circle"></i> Apply Computed Deductions</button>
          </form>
          
          <a href="{{ route(\'admin.deductions.summary\', [\'month\' => $month, \'year\' => $year]) }}" class="btn btn-sm btn-info text-white"><i class="bi bi-printer"></i> View Summary Report</a>
        </div>

        @if(session(\'success\'))
          <div class="alert alert-success py-2 px-3" style="font-size:0.8rem;border-radius:var(--r-sm);">{{ session(\'success\') }}</div>
        @endif

        <div class="table-responsive">
          <table class="table table-sm table-bordered table-hover" style="font-size:0.75rem;">
            <thead style="background:#f8fafc;">
              <tr>
                <th>Employee</th>
                <th>Designation</th>
                <th class="text-end">Gross Pay</th>
                <th class="text-end" style="color:#dc2626;">W/Tax</th>
                <th class="text-end" style="color:#2563eb;">GSIS</th>
                <th class="text-end" style="color:#059669;">PhilHealth</th>
                <th class="text-end" style="color:#b45309;">Pag-IBIG</th>
                <th class="text-end" style="color:#6d28d9;">SSS</th>
                <th class="text-end text-danger fw-bold">Total Gov\'t</th>
                <th class="text-end fw-bold">Net Pay</th>
              </tr>
            </thead>
            <tbody>
              @forelse($employees as $emp)
              <tr>
                <td class="fw-bold">{{ $emp->employee_name }}</td>
                <td class="text-muted">{{ $emp->designation ?? \'-\' }}</td>
                <td class="text-end" style="color:var(--accent);">₱{{ number_format($emp->gross_pay, 2) }}</td>
                <td class="text-end">₱{{ number_format($emp->withholding_tax_val, 2) }}</td>
                <td class="text-end">₱{{ number_format($emp->gsis_val, 2) }}</td>
                <td class="text-end">₱{{ number_format($emp->philhealth_val, 2) }}</td>
                <td class="text-end">₱{{ number_format($emp->pag_ibig_val, 2) }}</td>
                <td class="text-end">₱{{ number_format($emp->sss_val, 2) }}</td>
                <td class="text-end text-danger fw-bold">₱{{ number_format($emp->total_govt_ded, 2) }}</td>
                <td class="text-end fw-bold">₱{{ number_format($emp->net_pay, 2) }}</td>
              </tr>
              @empty
              <tr>
                <td colspan="10" class="text-center py-4 text-muted"><i class="bi bi-inbox fs-4 d-block"></i> No employees found for this period.</td>
              </tr>
              @endforelse
            </tbody>
            @if(count($employees) > 0)
            <tfoot style="background:#f0f6ff; font-weight:bold;">
              <tr>
                <td colspan="2" class="text-end">TOTALS</td>
                <td class="text-end">₱{{ number_format($stats[\'total_gross\'], 2) }}</td>
                <td class="text-end">₱{{ number_format($stats[\'total_wtax\'], 2) }}</td>
                <td class="text-end">₱{{ number_format($stats[\'total_gsis\'], 2) }}</td>
                <td class="text-end">₱{{ number_format($stats[\'total_philhealth\'], 2) }}</td>
                <td class="text-end">₱{{ number_format($stats[\'total_pagibig\'], 2) }}</td>
                <td class="text-end">₱{{ number_format($stats[\'total_sss\'], 2) }}</td>
                <td class="text-end text-danger">₱{{ number_format($stats[\'total_govt_ded\'], 2) }}</td>
                <td class="text-end">₱{{ number_format($stats[\'total_net_pay\'], 2) }}</td>
              </tr>
            </tfoot>
            @endif
          </table>
        </div>
      </div>

    </div>
  </div>

  <script>
    function toggleSidebar() {
      document.getElementById(\'sidebar\').classList.toggle(\'open\');
      document.getElementById(\'overlay\').classList.toggle(\'show\');
    }
    function closeSidebar() {
      document.getElementById(\'sidebar\').classList.remove(\'open\');
      document.getElementById(\'overlay\').classList.remove(\'show\');
    }
    
    document.getElementById(\'mobileMenuBtn\')?.addEventListener(\'click\', toggleSidebar);
    
    function updateClock() {
      const now = new Date();
      const timeStr = now.toLocaleTimeString(\'en-US\', { hour: \'numeric\', minute: \'2-digit\', second: \'2-digit\' });
      const clockEl = document.getElementById(\'liveClock\');
      if(clockEl) clockEl.textContent = timeStr;
    }
    setInterval(updateClock, 1000);
    updateClock();

    const toggleThemeBtn = document.getElementById(\'toggleTheme\');
    const themeIcon = document.getElementById(\'themeIcon\');
    
    // Check saved theme
    if (localStorage.getItem(\'theme\') === \'dark\') {
      document.body.classList.add(\'night-mode\');
      if(themeIcon) { themeIcon.classList.remove(\'bi-moon\'); themeIcon.classList.add(\'bi-sun\'); }
    }

    if(toggleThemeBtn) {
      toggleThemeBtn.addEventListener(\'click\', () => {
        document.body.classList.toggle(\'night-mode\');
        const isDark = document.body.classList.contains(\'night-mode\');
        localStorage.setItem(\'theme\', isDark ? \'dark\' : \'light\');
        
        if (isDark) {
          themeIcon.classList.remove(\'bi-moon\');
          themeIcon.classList.add(\'bi-sun\');
        } else {
          themeIcon.classList.remove(\'bi-sun\');
          themeIcon.classList.add(\'bi-moon\');
        }
      });
    }
  </script>
</body>
</html>
';

file_put_contents('resources/views/admin/deductions/index.blade.php', $html);
echo "Successfully created index.blade.php\n";
