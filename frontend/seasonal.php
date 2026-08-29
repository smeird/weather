<?php include('header.php'); ?>
<style>
  .seasonal-toolbar {
    display: grid;
    grid-template-columns: minmax(15rem, .7fr) minmax(24rem, 2.3fr);
    align-items: stretch;
    gap: .65rem;
  }
  .seasonal-control-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(7rem, 1fr));
    align-items: end;
    gap: .55rem;
  }
  .seasonal-control {
    display: grid;
    gap: .22rem;
    min-width: 0;
  }
  .seasonal-control label,
  .seasonal-picker-label {
    color: #68778b;
    font-size: .53rem;
    font-weight: 800;
    letter-spacing: .09em;
    text-transform: uppercase;
  }
  .seasonal-control select {
    width: 100%;
    min-height: 2.15rem;
    padding: .35rem 2rem .35rem .55rem;
    border: 1px solid #cbd5df;
    border-radius: .45rem;
    color: #25364b;
    background-color: #fff;
    font-size: .68rem;
  }
  .seasonal-year-picker {
    display: grid;
    gap: .42rem;
    min-width: 0;
    padding-left: .65rem;
    border-left: 1px solid #dce3ea;
  }
  .seasonal-year-picker-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .65rem;
  }
  .seasonal-selection-status {
    margin-left: .4rem;
    color: #7b8798;
    font-size: .58rem;
    font-weight: 600;
    letter-spacing: normal;
    text-transform: none;
  }
  .seasonal-presets,
  .seasonal-year-options {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: .28rem;
  }
  .seasonal-preset,
  .seasonal-year-option {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 1.9rem;
    border: 1px solid #d3dce6;
    border-radius: .42rem;
    color: #617187;
    background: #fff;
    font-family: Inter, system-ui, sans-serif;
    font-size: .6rem;
    font-weight: 750;
    transition: border-color .16s ease, box-shadow .16s ease, color .16s ease, background .16s ease;
  }
  .seasonal-preset {
    min-height: 1.65rem;
    padding: 0 .48rem;
    font-size: .55rem;
  }
  .seasonal-preset[aria-pressed="true"] {
    color: #fff;
    border-color: #173f69;
    background: #173f69;
  }
  .seasonal-preset:disabled {
    cursor: wait;
    opacity: .48;
  }
  .seasonal-year-option {
    gap: .34rem;
    min-width: 3.7rem;
    padding: 0 .55rem;
  }
  .seasonal-year-option::before {
    width: .46rem;
    height: .46rem;
    border: 2px solid var(--year-colour, #64748b);
    border-radius: 999px;
    background: transparent;
    content: "";
  }
  .seasonal-year-option[aria-pressed="true"] {
    color: #20344c;
    border-color: var(--year-colour, #64748b);
    background: #fff;
    box-shadow: inset 0 0 0 1px var(--year-colour, #64748b), 0 2px 7px rgba(15, 23, 42, .06);
  }
  .seasonal-year-option[aria-pressed="true"]::before {
    background: var(--year-colour, #64748b);
  }
  .seasonal-preset:hover,
  .seasonal-year-option:hover {
    border-color: #9fb4c8;
    color: #173f69;
  }
  .seasonal-preset:focus-visible,
  .seasonal-year-option:focus-visible {
    outline: 2px solid #60a5fa;
    outline-offset: 2px;
  }
  html.dark .seasonal-year-picker { border-left-color: #334155; }
  html.dark .seasonal-control label,
  html.dark .seasonal-picker-label,
  html.dark .seasonal-selection-status { color: #94a3b8; }
  html.dark .seasonal-control select,
  html.dark .seasonal-preset,
  html.dark .seasonal-year-option { color: #cbd5e1; border-color: #475569; background: #1f2937; }
  html.dark .seasonal-preset[aria-pressed="true"] { color: #fff; border-color: #3b82f6; background: #1d4ed8; }
  html.dark .seasonal-year-option[aria-pressed="true"] { color: #f8fafc; border-color: var(--year-colour, #94a3b8); background: #111827; }
  @media (max-width: 900px) {
    .seasonal-toolbar { grid-template-columns: 1fr; }
    .seasonal-year-picker { padding: .6rem 0 0; border-top: 1px solid #dce3ea; border-left: 0; }
    html.dark .seasonal-year-picker { border-top-color: #334155; border-left: 0; }
  }
  @media (max-width: 520px) {
    .seasonal-year-picker-head { align-items: flex-start; flex-direction: column; }
    .seasonal-presets { width: 100%; }
    .seasonal-preset { flex: 1; }
    .seasonal-year-options { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); }
    .seasonal-year-option { width: 100%; min-width: 0; padding: 0 .3rem; }
  }
</style>
<div class="site-workspace">
  <header class="workspace-header"><div><span class="workspace-eyebrow">Archive comparison</span><h1>Seasonal patterns</h1><p>Compare monthly temperature and rainfall profiles across any combination of years.</p></div><span class="workspace-badge"><i class="fas fa-layer-group"></i> Multi-year view</span></header>

  <div class="workspace-toolbar seasonal-toolbar">
    <div class="seasonal-control-grid">
      <div class="seasonal-control">
        <label for="type-select">Measure</label>
        <select id="type-select">
          <option value="temp">Temperature</option>
          <option value="rain">Rainfall</option>
        </select>
      </div>
      <div id="stat-container" class="seasonal-control">
        <label for="stat-select">Statistic</label>
        <select id="stat-select">
          <option value="avg">Average</option>
          <option value="min">Minimum</option>
          <option value="max">Maximum</option>
          <option value="median">Median</option>
          <option value="std">Std Dev</option>
        </select>
      </div>
    </div>
    <section class="seasonal-year-picker" aria-labelledby="seasonal-year-label">
      <div class="seasonal-year-picker-head">
        <div>
          <span id="seasonal-year-label" class="seasonal-picker-label">Compare years</span>
          <span id="year-selection-status" class="seasonal-selection-status" aria-live="polite">Loading archive…</span>
        </div>
        <div class="seasonal-presets" role="group" aria-label="Year selection presets">
          <button type="button" class="seasonal-preset" data-year-preset="3" aria-pressed="false" disabled>Latest 3</button>
          <button type="button" class="seasonal-preset" data-year-preset="5" aria-pressed="false" disabled>Latest 5</button>
          <button type="button" class="seasonal-preset" data-year-preset="all" aria-pressed="false" disabled>All years</button>
        </div>
      </div>
      <div id="year-options" class="seasonal-year-options" role="group" aria-label="Years to compare"></div>
    </section>
  </div>
  <section class="workspace-panel"><div class="workspace-panel-head"><div><h2 id="seasonal-chart-title">Monthly profile</h2><p id="seasonal-chart-subtitle">Selected years shown on one shared scale</p></div></div><div id="seasonal-chart" class="workspace-chart"></div></section>
  <section class="workspace-panel workspace-table-scroll"><table class="workspace-table">
    <thead class="bg-gray-50">
      <tr>
        <th class="px-4 py-2 text-left">Year</th>
        <th class="px-4 py-2 text-left">Month</th>
        <th id="value-header" class="px-4 py-2 text-left"></th>
      </tr>
    </thead>
    <tbody id="seasonal-table" class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700"></tbody>
  </table></section>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    var allData = {};
    var availableYears = [];
    var selectedYears = new Set();
    var yearColours = {};
    var yearOptions = document.getElementById('year-options');
    var selectionStatus = document.getElementById('year-selection-status');
    var presetButtons = Array.from(document.querySelectorAll('[data-year-preset]'));
    var statSelect = document.getElementById('stat-select');
    var typeSelect = document.getElementById('type-select');
    var statContainer = document.getElementById('stat-container');
    var valueHeader = document.getElementById('value-header');
    var chartTitle = document.getElementById('seasonal-chart-title');
    var chartSubtitle = document.getElementById('seasonal-chart-subtitle');

    function sameYears(left, right) {
      if (left.length !== right.length) return false;
      return left.every(function(year, index) { return year === right[index]; });
    }

    function selectedYearList() {
      return availableYears.filter(function(year) { return selectedYears.has(year); });
    }

    function latestYears(count) {
      return availableYears.slice(Math.max(0, availableYears.length - count));
    }

    function updateYearControls(message) {
      var selected = selectedYearList();
      Array.from(yearOptions.querySelectorAll('[data-year]')).forEach(function(button) {
        button.setAttribute('aria-pressed', selectedYears.has(button.dataset.year) ? 'true' : 'false');
      });

      presetButtons.forEach(function(button) {
        var preset = button.dataset.yearPreset;
        var presetYears = preset === 'all' ? availableYears : latestYears(Number(preset));
        button.setAttribute('aria-pressed', sameYears(selected, presetYears) ? 'true' : 'false');
      });

      if (message) {
        selectionStatus.textContent = message;
      } else if (selected.length === 1) {
        selectionStatus.textContent = selected[0] + ' selected';
      } else {
        selectionStatus.textContent = selected.length + ' years selected · ' + selected[0] + '–' + selected[selected.length - 1];
      }
    }

    function buildYearOptions() {
      var palette = WeatherCharts.getOptions().colors || ['#2563eb', '#0891b2', '#e8793f', '#6d5bd0', '#d24b4b', '#5b8c3a', '#c58a18', '#64748b'];
      yearOptions.innerHTML = '';
      availableYears.forEach(function(year, index) {
        var colourIndex = availableYears.length - 1 - index;
        yearColours[year] = palette[colourIndex % palette.length];
        var button = document.createElement('button');
        button.type = 'button';
        button.className = 'seasonal-year-option';
        button.dataset.year = year;
        button.textContent = year;
        button.style.setProperty('--year-colour', yearColours[year]);
        button.setAttribute('aria-pressed', selectedYears.has(year) ? 'true' : 'false');
        button.addEventListener('click', function() {
          if (selectedYears.has(year)) {
            if (selectedYears.size === 1) {
              updateYearControls('Keep at least one year selected');
              return;
            }
            selectedYears.delete(year);
          } else {
            selectedYears.add(year);
          }
          updateYearControls();
          render();
        });
        yearOptions.appendChild(button);
      });
      presetButtons.forEach(function(button) { button.disabled = false; });
      updateYearControls();
    }

    function applyPreset(preset) {
      if (!availableYears.length) return;
      var years = preset === 'all' ? availableYears : latestYears(Number(preset));
      selectedYears = new Set(years);
      updateYearControls();
      render();
    }

    function loadData() {
      selectionStatus.textContent = 'Loading archive…';
      fetch('backend/seasonal-data.php?stat=' + statSelect.value)
        .then(function(resp) {
          if (!resp.ok) throw new Error('Seasonal archive request failed');
          return resp.json();
        })
        .then(function(data) {
          allData = data;
          availableYears = Object.keys(data).sort(function(a, b) { return Number(a) - Number(b); });
          selectedYears = new Set(selectedYearList());
          if (selectedYears.size === 0 && availableYears.length) {
            selectedYears = new Set(latestYears(3));
          }
          buildYearOptions();
          render();
        })
        .catch(function() {
          selectionStatus.textContent = 'Archive data could not be loaded';
        });
    }

    function getStatLabel() {
      var map = { min: 'Min', max: 'Max', avg: 'Avg', median: 'Median', std: 'Std Dev' };
      return map[statSelect.value] || 'Avg';
    }

    function render() {
      var selected = selectedYearList();
      var tbody = document.getElementById('seasonal-table');
      tbody.innerHTML = '';
      var categories = [];
      var series = [];
      selected.forEach(function(year) {
        var rows = allData[year] || [];
        rows.forEach(function(row) {
          var val = typeSelect.value === 'temp' ? row.temp.toFixed(1) : row.totalRain.toFixed(1);
          var tr = document.createElement('tr');
          tr.innerHTML = '<td class="px-4 py-2">' + year + '</td>' +
            '<td class="px-4 py-2">' + row.month_name + '</td>' +
            '<td class="px-4 py-2">' + val + '</td>';
          tbody.appendChild(tr);
        });
        if (!categories.length) {
          categories = rows.map(function(r) { return r.month_name; });
        }

        series.push({
          name: year,
          color: yearColours[year],
          lineWidth: year === selected[selected.length - 1] ? 2 : 1.35,
          data: rows.map(function(r) {
            return typeSelect.value === 'temp' ? r.temp : r.totalRain;
          })
        });

      });

      if (typeSelect.value === 'temp') {
        valueHeader.textContent = getStatLabel() + ' Temp (°C)';
        chartTitle.textContent = getStatLabel() + ' monthly temperature';
        chartSubtitle.textContent = selected.length + ' selected years · ' + selected[0] + '–' + selected[selected.length - 1] + ' · °C';
        WeatherCharts.chart('seasonal-chart', {
          chart: {
            type: 'spline',
            backgroundColor: 'transparent',
            plotBackgroundColor: 'transparent',
            plotBorderWidth: 0,
            plotShadow: false
          },
          title: { text: null },
          xAxis: { categories: categories },
          yAxis: { title: { text: 'Temperature (°C)' } },
          tooltip: { shared: true, valueSuffix: ' °C' },
          legend: { align: 'center', verticalAlign: 'bottom' },
          plotOptions: { series: { marker: { enabled: false, states: { hover: { enabled: true, radius: 3 } } } } },
          series: series,
          credits: { enabled: false }
        });
      } else {
        valueHeader.textContent = 'Total Rain (mm)';
        chartTitle.textContent = 'Total monthly rainfall';
        chartSubtitle.textContent = selected.length + ' selected years · ' + selected[0] + '–' + selected[selected.length - 1] + ' · millimetres';
        WeatherCharts.chart('seasonal-chart', {
          chart: {
            type: 'column',
            backgroundColor: 'transparent',
            plotBackgroundColor: 'transparent',
            plotBorderWidth: 0,
            plotShadow: false
          },
          title: { text: null },
          xAxis: { categories: categories },
          yAxis: { min: 0, title: { text: 'Rainfall (mm)' } },
          tooltip: { shared: true, valueSuffix: ' mm' },
          legend: { align: 'center', verticalAlign: 'bottom' },
          plotOptions: { column: { borderWidth: 0, borderRadius: 2, groupPadding: .08, pointPadding: .03 } },
          series: series,
          credits: { enabled: false }
        });
      }
    }

    statSelect.addEventListener('change', loadData);
    presetButtons.forEach(function(button) {
      button.addEventListener('click', function() { applyPreset(button.dataset.yearPreset); });
    });
    typeSelect.addEventListener('change', function() {
      if (typeSelect.value === 'rain') {
        statContainer.classList.add('hidden');
        render();
      } else {
        statContainer.classList.remove('hidden');
        render();
      }
    });

    loadData();
  });
</script>
<?php include('footer.php'); ?>
