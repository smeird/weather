document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('table[data-tabulator]').forEach(table => {
    const headers = Array.from(table.querySelectorAll('thead th')).map(th => ({
      title: th.textContent.trim(),
      field: th.textContent.trim().toLowerCase().replace(/[^a-z0-9]+/g, '_')
    }));
    const data = Array.from(table.querySelectorAll('tbody tr')).map(tr => {
      const cells = tr.querySelectorAll('td');
      const row = {};
      headers.forEach((h, i) => {
        row[h.field] = cells[i] ? cells[i].textContent.trim() : '';
      });
      return row;
    });
    const container = document.createElement('div');
    container.className = 'w-full';
    table.parentNode.replaceChild(container, table);
    new Tabulator(container, {
      data,
      columns: headers,
      layout: 'fitDataTable'
    });
  });
});
