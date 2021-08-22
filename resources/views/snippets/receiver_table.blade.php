<style>
    th {
        vertical-align: center;
        width: 30mm;
    }
    td {
        vertical-align: center;
        width: 60mm;
    }
</style>

<table class="table-sm">
    <tbody>
    <tr>
        <th>STATE LICENSE1 #</th>
        <td>{{ strstr($invoice->customer->license,',', true) }}</td>
    </tr>
    <tr>
        <th>STATE LICENSE2 #</th>
        <td>{{ strstr($invoice->customer->license2,',', true) }}</td>
    </tr>
    <tr>
        <th>TYPE OF LICENSE</th>
        <td>Retailer License</td>
    </tr>
    <tr>
        <th> BUSINESS NAME</th>
        <td>{{ $invoice->customer->name }}</td>
    </tr>
    <tr>
        <th>BUSINESS ADDRESS</th>
        <td>{{ $invoice->customer->street }}</td>
    </tr>
    <tr>
        <th> CITY, STATE, ZIP CODE</th>
        <td>{{  $invoice->customer->city }}, CA {{  $invoice->customer->zip }}</td>
    </tr>
    <tr>
        <th>PHONE NUMBER</th>
        <td>{{ $invoice->customer->phone }}</td>
    </tr>
    <tr>
        <th>CONTACT NAME</th>
        <td>contact</td>
    </tr>
    </tbody>
</table>
