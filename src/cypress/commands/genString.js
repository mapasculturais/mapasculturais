function generateString(tamanho) {
    const caracteres = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
    let result = '';
    const comprimentoCaracteres = caracteres.length;

    for (let i = 0; i < tamanho; i++) {
        result += caracteres.charAt(Math.floor(Math.random() * comprimentoCaracteres));
    }

    return result;
}

module.exports = { generateString };