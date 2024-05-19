const { confirmRecaptcha } = require("../../commands/recaptcha");
const { loginWith } = require("../../commands/login");
const { generateCPF } = require("../../commands/genCPF");
const { generateString} = require("../../commands/genString")

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
    it("click no botão \"Fazer cadastro\"", () => {
        cy.visit("/autenticacao/");
        cy.contains("Fazer cadastro").click();
        cy.url().should("include", "/autenticacao/register/");
    });

    it("Garante que não haja contas com o mesmo cpf ou email", () => {
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

        cy.wait(1000);

        cy.contains("Este CPF já esta em uso. Tente recuperar a sua senha.");
        cy.contains("Este endereço de email já está em uso. Tente recuperar a sua senha.");
    });

    it("Garante que cpf seja valido", () => {
        cy.visit("/autenticacao/register/");
        cy.get("#email").type("fake4@email.com");
        cy.get("#email").should("have.value", "fake4@email.com");
        cy.get("#cpf").type("68861193541");
        cy.get("#pwd").type("Fakepassword@10");
        cy.get("#pwd").should("have.attr", "type", "password");
        cy.get(".seePassword").click({ multiple: true });
        cy.get("#pwd").should("have.value", "Fakepassword@10");
        cy.get("#pwd-check").type("Fakepassword@10");
        cy.wait(1000);
        confirmRecaptcha();
        cy.wait(2000);
        cy.contains("Continuar").click();

        cy.wait(1000);

        cy.contains("Por favor, informe um cpf válido.");
    });

    
    it("Continuar cadastro", () => {
        cy.visit("/autenticacao/register/");  

        let email = generateString(5) + "@email.com";  
        cy.get("#email").type(email);
        cy.get("#email").should("have.value", email);

        let cpf = generateCPF();
        cy.get("#cpf").type(cpf);

        cy.get("#pwd").type("Fakepassword@10");
        cy.get("#pwd").should("have.attr", "type", "password");
        cy.get(".seePassword").click({ multiple: true });
        cy.get("#pwd").should("have.value", "Fakepassword@10");
        cy.get("#pwd-check").type("Fakepassword@10");
        cy.wait(1000);
        confirmRecaptcha();
        cy.wait(2000);
        cy.contains("Continuar").click();

        cy.wait(4000);

        cy.get(".field__title ~ input").type("Joao");
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

        loginWith(email, "Fakepassword@10");
    });
});