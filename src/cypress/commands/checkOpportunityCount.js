function checkOpportunityCount() {
        cy.get('.foundResults').then(($foundResultsVar) => {
                let resultsTextArray = $foundResultsVar.text().split(" ");
                let resultsCount = Number(resultsTextArray[0]);
                
                cy.get(".upper.opportunity__color").should("have.length", resultsCount);
                cy.wait(1000);
                cy.contains(resultsCount + " Oportunidades encontradas");
        });
}

module.exports = { checkOpportunityCount };