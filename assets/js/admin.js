document.addEventListener('DOMContentLoaded', function() {

    document.body.classList.toggle('adi-body', true);
    
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


    /**
     * Refresh table data on refresh button click.
     */
    document.getElementById('refresh-data-button').addEventListener('click', function(){

        let data = new FormData();
        data.append('action', 'refresh_data');

        fetch (handle.admin_url, {
            method: 'POST',
            body: data
        })

        .then(response => response.json())

        .then(data => {
            let rowsArray = Object.values(data.data.data.rows);

            let headers = data.data.data.headers
                .map(header => `<th>${header}</th>`)
                .join('');

            let rows = rowsArray
                .map(row => {
                    let cells = data.data.data.headers
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
                    <h1 class="table-title">${data.data.title}</h1>
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
        })

        .catch(error => {
            console.error('Error:', error);
        });
    });
});