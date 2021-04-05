MapasCulturais.Charts = {
    charts: {}
};

MapasCulturais.getChartColors = function (quantity = 1) {

    let pointer = MapasCulturais.chartColors.pointer;
    let colors = [];

    for (let i = 0; i < quantity; i++) {
        colors.push(MapasCulturais.chartColors.colors[pointer]);

        pointer++;
        if (pointer >= MapasCulturais.chartColors.colors.length) {
            pointer = 0;
        }
    }
    MapasCulturais.chartColors.pointer = pointer;

    return colors;

}