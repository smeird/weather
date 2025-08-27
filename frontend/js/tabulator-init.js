document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('table[data-tabulator]').forEach(table => {
    const headerRows = Array.from(table.querySelectorAll('thead tr'));
    let columns = [];
    let flatHeaders = [];
    const cellFormatter = cell => {
      const data = cell.getData();
      const field = cell.getField();
      const cls = data[field + '_class'];
      if (cls) {
        cls.split(' ').forEach(c => cell.getElement().classList.add(c));
      }
      return data[field];
    };

    if (headerRows.length > 1) {
      const topCells = Array.from(headerRows[0].children);
      const secondCells = Array.from(headerRows[1].children);
      let secondIndex = 0;

      topCells.forEach(cell => {
        const title = cell.textContent.trim();
        const colspan = parseInt(cell.getAttribute('colspan') || '1', 10);
        const rowspan = parseInt(cell.getAttribute('rowspan') || '1', 10);

        if (colspan > 1) {
          const groupCols = [];
          for (let i = 0; i < colspan; i++) {
            const subCell = secondCells[secondIndex++];
            const subTitle = subCell.textContent.trim();
            const field = subTitle.toLowerCase().replace(/[^a-z0-9]+/g, '_');
            groupCols.push({ title: subTitle, field, formatter: cellFormatter });
            flatHeaders.push({ title: subTitle, field });
          }
          columns.push({ title, columns: groupCols });
        } else {
          const field = title.toLowerCase().replace(/[^a-z0-9]+/g, '_');
          columns.push({ title, field, formatter: cellFormatter });
          flatHeaders.push({ title, field });
          if (rowspan === 1) {
            secondIndex++;
          }
        }
      });
    } else {
      flatHeaders = Array.from(table.querySelectorAll('thead th')).map(th => {
        return {
          title: th.textContent.trim(),
          field: th.textContent.trim().toLowerCase().replace(/[^a-z0-9]+/g, '_')
        };
      });
      columns = flatHeaders.map(h => ({ ...h, formatter: cellFormatter }));
    }

    const data = Array.from(table.querySelectorAll('tbody tr')).map(tr => {
      const cells = tr.querySelectorAll('td');
      const row = {};
      flatHeaders.forEach((h, i) => {
        row[h.field] = cells[i] ? cells[i].textContent.trim() : '';
        row[h.field + '_class'] = cells[i] ? cells[i].getAttribute('class') || '' : '';
      });
      return row;
    });

    const container = document.createElement('div');
    container.className = 'w-full';
    table.parentNode.replaceChild(container, table);
    new Tabulator(container, {
      data,
      columns,
      layout: 'fitDataTable'
    });
  });
});
