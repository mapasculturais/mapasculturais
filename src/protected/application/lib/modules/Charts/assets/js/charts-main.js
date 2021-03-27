MapasCulturais.Charts = {
    dynamicColors: function() {

        var colors = [
            '#333333',
            '#1c5690',
            '#b3b921',
            '#1dabc6',
            '#e83f96',
            '#cc0033',
            '#9966cc',
            '#40b4b4',
            '#cc9933',
            '#cc3333',
            '#66cc66',
            '#003c46',
            '#d62828',
            '#5a189a',
            '#00afb9',
            '#38b000',
            '#3a0ca3',
            '#489fb5',
            '#245501',
            '#708d81',
            '#00bbf9',
            '#f15bb5',
            '#ffdab9',
            '#5f0f40',
            '#e9ff70',
            '#fcf6bd',
            '#4a5759',
            '#06d6a0',
            '#cce3de',
            '#f3ac01'
        ];

        var rand = Math.floor(Math.random() * (colors.length - 1));
        return colors[rand];
    },
    charts: {}
};