document.addEventListener('DOMContentLoaded', function() {

    /**
     * Display API response in HTML table format
     */

    let data = handle.api_response;

    let rowsArray = Object.values(data.data.rows);

    let headers = data.data.headers
        .map(header => `<th>${header}</th>`)
        .join('');

    let rows = rowsArray
        .map(row => {
            let cells = data.data.headers
                .map(header => {
                    let key;
                    switch (header) {
                        case "ID": key = 'id'; break;
                        case "First Name": key = 'fname'; break;
                        case "Last Name": key = 'lname'; break;
                        case "Email": key = 'email'; break;
                        case "Date": key = 'date'; break;
                        default: key = header.toLowerCase().replace(/ /g, '_');
                    }
                    let value = row[key] || '';
                    return `<td>${value}</td>`;
                })
                .join('');
            return `<tr>${cells}</tr>`;
        })
        .join('');

    let output = `
        <div class="table-wrapper">
            <h1 class="table-title">${data.title}</h1>
            <table>
                <thead>
                    <tr>${headers}</tr>
                </thead>
                <tbody>
                    ${rows}
                </tbody>
            </table>
        </div>
    `;

    document.getElementById('adi_data-output').innerHTML = output;
});