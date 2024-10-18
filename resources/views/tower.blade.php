<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tower of Hanoi</title>
</head>
<body>
<h1>Tower of Hanoi</h1>

<div id="pegs">
    <div>Peg 1: <span id="peg1"></span></div>
    <div>Peg 2: <span id="peg2"></span></div>
    <div>Peg 3: <span id="peg3"></span></div>
</div>

<input type="number" id="from-peg" placeholder="From peg (1-3)" min="1" max="3">
<input type="number" id="to-peg" placeholder="To peg (1-3)" min="1" max="3">
<button id="move-button">Move Disk</button>
<button id="reset-game">Reset Game</button>

<p id="message"></p>

<script>
    function loadGameState() {
        fetch('{{route('state')}}')
            .then(res => res.json())
            .then(data => {
                document.getElementById('peg1').innerText = data.pegs[0].join(', ');
                document.getElementById('peg2').innerText = data.pegs[1].join(', ');
                document.getElementById('peg3').innerText = data.pegs[2].join(', ');
            });
    }

    document.getElementById('move-button').addEventListener('click', () => {
        const from = document.getElementById('from-peg').value;
        const to = document.getElementById('to-peg').value;

        const url = `{{ route('move', ['from' => ':from', 'to' => ':to']) }}`
            .replace(':from', from)
            .replace(':to', to);
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
        })
            .then(response => response.json())
            .then(data => {
                document.getElementById('peg1').innerText = data.pegs[0].join(', ');
                document.getElementById('peg2').innerText = data.pegs[1].join(', ');
                document.getElementById('peg3').innerText = data.pegs[2].join(', ');
                if (data.isGameComplete) {
                    document.getElementById('message').innerText = 'Congratulations! You won.';
                } else {
                    document.getElementById('message').innerText = '';
                }
            })
            .catch(error => {
                document.getElementById('message').innerText = `Error in moving disk`;
            });
    });


    document.getElementById('reset-game').addEventListener('click', () => {
        fetch('{{route('clean')}}', {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
            .then(res => res.json())
            .then(data => {
                document.getElementById('message').innerText = data.message;
                loadGameState();
            });
    });

    loadGameState();
</script>
</body>
</html>
