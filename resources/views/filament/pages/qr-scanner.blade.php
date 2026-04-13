<!DOCTYPE html>
<html>
<head>
    <title>QR Scanner</title>

    <script src="https://unpkg.com/html5-qrcode"></script>

    <style>
        body{
            font-family: Arial;
            background:#f0fdfa;
            display:flex;
            justify-content:center;
            align-items:center;
            height:100vh;
        }

        #reader{
            width:350px;
        }

        h2{
            text-align:center;
            margin-bottom:15px;
        }
    </style>
</head>

<body>

<div>
    <h2>Scan Member QR Code</h2>

    <div id="reader"></div>
</div>

<script>

function onScanSuccess(decodedText, decodedResult) {

    console.log(`Code scanned = ${decodedText}`);

    // redirect to scan result
    window.location.href = decodedText;
}

let html5QrcodeScanner = new Html5QrcodeScanner(
    "reader",
    { fps: 10, qrbox: 250 }
);

html5QrcodeScanner.render(onScanSuccess);

</script>

</body>
</html>