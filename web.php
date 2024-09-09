<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script>
        $(document).ready(function() {
            $('form').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'http://localhost/CLI/index.php',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        command: $('#command').val()
                    }),
                    success: function(data) {
                        console.log(data);
                        $('#console').append(data + '<br>');
                        $('#console').scrollTop($('#console')[0].scrollHeight);
                    }
                });
            });
        });

        $(document).on('click', '#clear', function() {
            $('#console').html('');
        });
    </script>
    <style>
        .console {
            background-color: black;
            color: white;
            padding: 10px;
            height: 440px;
            overflow-y: scroll;
        }
    </style>
</head>
<body>
    <h1>Command Injection</h1>
    <form action="" >
        <input type="text" id="command" value="--script=2002 --year=3000">
        <input type="submit" value="execute">
    </form>
    <button type="none" id="clear">Clear console</button>


    <h2>Console output</h2>
    <pre>
        <div class="console" id="console"></div>
    </pre>
</body>
</html>