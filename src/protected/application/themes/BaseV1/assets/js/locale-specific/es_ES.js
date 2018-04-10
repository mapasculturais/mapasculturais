

// Create a human readble versio of the date rules of a event
MapasCulturais.createHumanReadableOccurrences = function(frequency, mdate_s, mdate_e, weekDays, hour) {
    
    var human = '';
    
    if (frequency == 'once') {
        if (!mdate_s) return '...';
        human += 'Dia ' + mdate_s.format('D [de] MMMM [de] YYYY');
    } else {

        if (!mdate_s || !mdate_e) return '...';

        if (frequency == 'daily') {
            human += 'Diariamente';
        } else if (frequency == 'weekly') {


            if (weekDays.length > 0) {

                if (weekDays[0] == '0' || weekDays[0] == '6') {
                    human += 'Todo ';
                } else {
                    human += 'Todo ';
                }

                var count = 1;
                $.each(weekDays, function(i, v) {
                    var wformat = weekDays.length > 1 ? 'ddd' : 'dddd';
                    human += moment().day(v).format(wformat);
                    count ++;
                    if (count == weekDays.length)
                        human += ' y ';
                    else if (count < weekDays.length)
                        human += ', '
                });
            }
        }

        if (mdate_s.year() != mdate_e.year()) {
            human += ' del ' + mdate_s.format('D [de] MMMM [de] YYYY') + ' al ' + mdate_e.format('D [de] MMMM [de] YYYY');
        } else {
            if (mdate_s.month() != mdate_e.month()) {
                human += ' del ' + mdate_s.format('D [de] MMMM') + ' al ' + mdate_e.format('D [de] MMMM [de] YYYY');
            } else {
                human += ' del ' + mdate_s.format('D') + ' al ' + mdate_e.format('D [de] MMMM [de] YYYY');
            }
        }


    }

    if (hour) {
        if (hour.substring(0,2) == '01')
            human += ' a ' + hour;
        else
            human += ' a las ' + hour;
    }

    return human;
    
};

