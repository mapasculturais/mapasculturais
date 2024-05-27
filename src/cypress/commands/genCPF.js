function generateCPF() {
    function rand(min, max) {
        return Math.floor(Math.random() * (max - min + 1)) + min;
    }

    function calcularDigito(digitos) {
        let sum = 0;
        for (let i = 0; i < digitos.length; i++) {
            sum += digitos[i] * (digitos.length + 1 - i);
        }
        let rest = sum % 11;
        return rest < 2 ? 0 : 11 - rest;
    }

    let n = [];
    for (let i = 0; i < 9; i++) {
        n.push(rand(0, 9));
    }

    let d1 = calcularDigito(n);
    n.push(d1);

    let d2 = calcularDigito(n);
    n.push(d2);

    return n.join('');
}

module.exports = { generateCPF };