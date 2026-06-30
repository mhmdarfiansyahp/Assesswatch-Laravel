<!DOCTYPE html>
<html>

<head>
    <style>
        @page {
            margin: 0;
        }

        body {
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            height: 100%;
        }

        img {
            width: 100%;
            height: auto;
            object-fit: contain;
        }
    </style>
</head>

<body>

    <div class="container">
        <img src="{{ $src }}">
    </div>

</body>

</html>