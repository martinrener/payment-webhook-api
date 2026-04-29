<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Payments</title>
    </head>
    <body>
        <h1>Payments</h1>
        <table>
            <thead>
                <tr>
                    <th>payment_id</th>
                    <th>event</th>
                    <th>currency</th>
                    <th>amount</th>
                    <th>user_id</th>
                    <th>last_event_id</th>
                </tr>
            </thead>
            <tbody id="payments-tbody">
                @foreach($payments as $payment)
                    <tr onclick="loadEvents('${payment.payment_id}')">
                        <td>{{ $payment['payment_id'] }}</td>
                        <td>{{ $payment['event'] }}</td>
                        <td>{{ $payment['currency'] }}</td>
                        <td>{{ $payment['amount'] }}</td>
                        <td>{{ $payment['user_id'] }}</td>
                        <td>{{ $payment['last_event_id'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div id="events-container"></div>
    </body>
</html>

<script>
    function loadPayments() {
        fetch('/api/payments')
            .then(response => response.json())
            .then(payments => {
                const tbody = document.getElementById('payments-tbody');
                tbody.innerHTML = '';
                payments.forEach(payment => {
                    tbody.innerHTML += `<tr onclick="loadEvents('${payment.payment_id}')">
                        <td>${payment.payment_id}</td>
                        <td>${payment.event}</td>
                        <td>${payment.currency}</td>
                        <td>${payment.amount}</td>
                        <td>${payment.user_id}</td>
                        <td>${payment.last_event_id}</td>
                    </tr>`;
                });
            });
    }

    setInterval(loadPayments, 5000);
    loadPayments();

    function loadEvents(payment_id) {
        fetch(`api/payments/${payment_id}/events`)
            .then(response => response.json())
            .then(events => {
                const div = document.getElementById('events-container');
                div.innerHTML = '';
                div.innerHTML = `<h2>Events for payment: <span id="selected-payment">${payment_id}</span></h2>
                            <table>
                                <thead>
                                    <tr>
                                        <th>event_id</th>
                                        <th>event</th>
                                        <th>currency</th>
                                        <th>amount</th>
                                        <th>timestamp</th>
                                        <th>received_at<th/>
                                    </tr>
                                </thead>
                                <tbody id="events-tbody"></tbody>
                            </table>`;
                const tbody = document.getElementById('events-tbody');
                events.forEach(event => {
                    tbody.innerHTML += `<tr onclick="loadEvents('${payment.payment_id}')">
                        <td>${event.event_id}</td>
                        <td>${event.currency}</td>
                        <td>${event.amount}</td>
                        <td>${event.amount}</td>
                        <td>${event.timestamp}</td>
                        <td>${event.received_at}</td>
                    </tr>`;
                });
            });

    }
</script>
