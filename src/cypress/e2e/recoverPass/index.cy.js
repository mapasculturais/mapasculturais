const { confirmRecaptcha } = require("../../commands/recaptcha");

describe("LoginPage", () => {
    beforeEach(() => {
        cy.visit("/");

        cy.intercept("POST", "https://www.google.com/recaptcha/api2/userverify", (req) => {
            req.reply({ captchaPassed: true });
        });
    });

    it("clicks the link \"Entrar\"", () => {
        cy.contains("Entrar").click();
        cy.url().should("include", "/autenticacao/");
    });
});

describe("RecoverPassword", () => {
    beforeEach(() => {
        cy.visit("/autenticacao/");
    });

    it("clicks the link \"Esqueci minha senha\", preenche o input email e click no botão \"Alterar senha\"", () => {
        cy.get("#multiple-login-recover").click();
        cy.get("#email").type("seuemail@email.com");

        cy.wait(1000);

        confirmRecaptcha();

        cy.wait(1000);

        cy.contains("Alterar senha").click();

        cy.wait(1000);

        cy.contains("Email não encontrado");
    });
});