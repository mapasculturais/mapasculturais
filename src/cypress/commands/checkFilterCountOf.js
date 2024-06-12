/* Checa a contagem de um elemento específico na página de filtros. */
function checkFilterCountOf(element) {
        cy.get('.foundResults').then(($foundResults) => {
                const countPerPage = 20;
                var resultsTextArray = $foundResults.text().split(" ");
                var resultsCount = Number(resultsTextArray[0]);
                const resultsCountPerPage = resultsCount < countPerPage ? resultsCount : countPerPage;

                switch (element) {
                        case "opportunity":
                                cy.get("span.upper." + element + "__color").should("have.length", resultsCountPerPage);
                                cy.wait(1000);
                                cy.contains(resultsCount + " Oportunidades encontradas");
                                
                                break;
                        
                        case "project":
                                cy.get("span.upper." + element + "__color").should("have.length", resultsCountPerPage);
                                cy.wait(1000);
                                cy.contains(resultsCount + " Projetos encontrados");
        
                                break;
                        
                        case "space":
                                cy.get("span.upper." + element + "__color").should("have.length", resultsCountPerPage);
                                cy.wait(1000);
                                cy.contains(resultsCount + " Espaços encontrados");
                
                                break;
                        
                        default:
                                cy.log("[-] Tipo inválido, use \"opportunity\", \"space\" ou \"project\"");
                                cy.contains("FORCE ERROR");
                                
                                break;
                }
        });
}

module.exports = { checkFilterCountOf };