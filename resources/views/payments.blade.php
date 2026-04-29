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
                    <th>user_id</th>
                    <th>last_event_id</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                    <tr>
                        <td>{{ $payment[payment_id] }}</td>
                        <td>{{ $payment[event] }}</td>
                        <td>{{ $payment[currency] }}</td>
                        <td>{{ $payment[user_id] }}</td>
                        <td>{{ $payment[last_event_id] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </body>
</html>
