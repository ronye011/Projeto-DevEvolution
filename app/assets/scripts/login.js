window.addEventListener("load", async function(event) {
    try {
        const response = await fetch('./../app/routes/routerInterface.php?route=Login/Valid', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        });

        const text = await response.text();
        const result = JSON.parse(text);

        if (result.status === 'success') {
            window.location.href = '/sistema/public/home.html'; // Redireciona para página protegida
        }
    } catch (error) {
    }
});

document.getElementById('login').addEventListener('submit', async function (e) {
    e.preventDefault();

    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;

    const response = await fetch('./../app/routes/routerInterface.php?route=Login/Access', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({email, password})
    });

    const text = await response.text();
    const result = JSON.parse(text);

    if (result.status == 'success') {
        window.location.href = '/sistema/public/home.html'; // Redireciona para página protegida
    } else {
        notificacao(result.status, result.message);
    }
});