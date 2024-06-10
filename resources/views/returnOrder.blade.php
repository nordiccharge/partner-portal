<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <title>Returformular</title>
    <script async src="https://tally.so/widgets/embed.js"></script>
    <style type="text/css">
        html { margin: 0; height: 100%; overflow: hidden; }
        iframe { position: absolute; top: 0; right: 0; bottom: 0; left: 0; border: 0; }
    </style>
</head>
<body>
<iframe data-tally-src="https://tally.so/r/m6kd6A?email={{$order->customer_email}}&order={{$order->id}}&name={{$order->customer_first_name . ' ' . $order->customer_last_name}}&address={{$order->shipping_address . ', ' . $order->postal->postal . ' ' . $order->city->name}}&phone={{$order->customer_phone}}" width="100%" height="100%" frameborder="0" marginheight="0" marginwidth="0" title="Returformular"></iframe>
</body>
</html>
