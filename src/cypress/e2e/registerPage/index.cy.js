const { confirmRecaptcha } = require("../../commands/recaptcha");
const { loginWith } = require("../../commands/login");
describe("LoginPage", () => {
    beforeEach(() => {
        cy.visit("/");
    });

    it("click no botão \"Entrar\"", () => {
        cy.contains("Entrar").click();
        cy.url().should("include", "/autenticacao/");
    });
});

describe("RegisterPage", () => {
    beforeEach(() => {
        cy.visit("/autenticacao/");
    });

    it("click no botão \"Fazer cadastro\"", () => {
        cy.contains("Fazer cadastro").click();
        cy.url().should("include", "/autenticacao/register/");
    });

    // Ao gerar fazer um novo teste de cadastro, é necessário alterar o email e cpf, pois já foram cadastrados.
    it("click nos campos de cadastro, captcha e no botão \"Continuar\" para continuar cadastro", () => {
        cy.visit("/autenticacao/register/");
        cy.get("#email").type("fake3@email.com");
        cy.get("#email").should("have.value", "fake3@email.com");
        cy.get("#cpf").type("68861193544");
        cy.get("#pwd").type("Fakepassword@10");
        cy.get("#pwd").should("have.attr", "type", "password");
        cy.get(".seePassword").click({ multiple: true });
        cy.get("#pwd").should("have.value", "Fakepassword@10");
        cy.get("#pwd-check").type("Fakepassword@10");
        cy.wait(1000);
        confirmRecaptcha();
        cy.wait(2000);
        cy.contains("Continuar").click();

        cy.wait(2000);
        cy.wait(2000);
        cy.get(".field__title ~ input").type("Maria");
        cy.get(".field__shortdescription textarea").type("Test description");
        cy.get(".v-popper > .button").click();
        cy.contains("Arte Digital").click();
        cy.contains("Confirmar").click();
        cy.wait(1000);
        confirmRecaptcha();
        cy.wait(2000);
        cy.contains("Criar cadastro").click();
        cy.wait(2000);
        cy.contains("Acessar meu cadastro").click();

        loginWith("fake3@email.com", "Fakepassword@10");
    });
});