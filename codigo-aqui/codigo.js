document.addEventListener('DOMContentLoaded', () => {
    const apiUrl = 'https://seu-dominio.com/vendas.php'; // Substitua pela URL correta da sua API

    // Função para gerar o ticket e salvar no localStorage
    const gerarTicket = () => {
        const ticket = localStorage.getItem('ticket');
        
        if (!ticket) {
            // Se o ticket ainda não foi gerado, chama a API
            fetch(apiUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    // Qualquer dado adicional que você precise enviar para a API
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Armazena o ticket no localStorage
                    localStorage.setItem('ticket', data.ticket);
                    console.log('Venda realizada com sucesso. Ticket:', data.ticket);
                } else {
                    console.error('Erro ao realizar a venda:', data.error || data.errors);
                }
            })
            .catch(error => {
                console.error('Erro na requisição:', error);
            });
        } else {
            console.log('Ticket já gerado:', ticket);
        }
    };

    // Chama a função para gerar o ticket
    gerarTicket();

    // Listener para resetar o ticket caso o usuário deseje fazer uma nova compra
    document.getElementById('resetTicket').addEventListener('click', () => {
        localStorage.removeItem('ticket');
        console.log('Ticket resetado. Pronto para gerar um novo.');
    });
});
