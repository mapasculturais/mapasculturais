function checkSpaceCount() {
        cy.get('.foundResults').then(($foundResults) => {
                let resultsTextArray = $foundResults.text().split(" ");
                let resultsCount = Number(resultsTextArray[0]);
        
                cy.get(".upper.space__color").should("have.length", resultsCount);
                cy.wait(1000);
                cy.contains(resultsCount + " Espaços encontrados");
        });
}

module.exports = { checkSpaceCount };