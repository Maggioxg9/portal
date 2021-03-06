(function($)
{
    //global properties, depending on current language
    var MonthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    var FirstDayOfWeek = 0;

    $.fn.calendar = function(initialDate)
    {
        var $this = $(this);
        var selectedDate = initialDate || new Date();
        var selectedMonth = selectedDate.getMonth();
        var selectedYear = selectedDate.getFullYear();

        return this.each(function()
        {
        });
    };

} (jQuery));

var getValue = function()
            {
                return selectedDate;
            };

            var setValue = function(date)
            {
                if (date == null)
                {
                    selectedDate = null;
                    return;
                }

                selectedDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
                selectedMonth = getMonth();
                selectedYear = getYear();
                refreshMonthTitle();
                refreshDayTable();
            };


var getDay = function()
            {
                return selectedDate.getDate();
            };

            var getWeekDay = function()
            {
                return selectedDate.getDay();
            };

            var getMonth = function()
            {
                return selectedDate.getMonth();
            };

            var getYear = function()
            {
                return selectedDate.getFullYear();
            };

            var setSelectedMonth = function(monthNum)
            {
                if (monthNum == -1 && selectedMonth == 0)
                {
                    selectedYear--;
                    selectedMonth = 11;
                }
                else if (monthNum == 12 && selectedMonth == 11)
                {
                    selectedYear++;
                    selectedMonth = 0;
                }
                else if (monthNum >= 0 && monthNum <= 11)
                    selectedMonth = monthNum;
                else
                    return;

                refreshMonthTitle();
                refreshDayTable();
            };

            var setSelectedYear = function(yearNum)
            {
                selectedYear = yearNum;
                refreshMonthTitle();
                refreshDayTable();
            };
            var getContentTable = function () {
                return $this.find('table');
            };

var refreshMonthTitle = function()
            {
                var monthTitle = $('#caltitle').text(MonthNames[selectedMonth] + ', ' + selectedYear);
            };

            var refreshDayTable = function () {
                var table = getContentTable();
                var month = selectedMonth;
                var year = selectedYear;

                var startd = new Date(year, month, 1);
                var d1 = FirstDayOfWeek;
                var d2 = startd.getDay();
                var diff = d1 < d2 ? d2 - d1 : d1 + 7;
                startd.setDate(startd.getDate() - diff);

                for (var j = 1; j < 7; j++) {
                    var row = table[0].rows[j];
                    for (var i = 0; i < 7; i++) {
                        var dy = startd.getDate();
                        var wd = startd.getDay();
                        var md = startd.getMonth();
                        var cell = $(row.cells[i]).text(dy);

                        cell.removeClass();
                        if (startd.valueOf() == selectedDate.valueOf())
                            //cell.addClass('mopCalendarDaySelected');
                        else if (md != month)
                            //cell.addClass('mopCalendarDayOdd');
                        else if (wd == 0 || wd == 6)
                            //cell.addClass('mopCalendarDayRed');

                        dy++;
                        startd.setDate(dy);
                    }
                }
            };

var onHeaderClick = function(e)
            {
                if (e.target)
                {
                    var target = $(e.target);
            
                    if (target.hasClass('calprevbtn'))
                        setSelectedMonth(selectedMonth - 1);
                    else if (target.hasClass('calnextbtn'))
                        setSelectedMonth(selectedMonth + 1);
                }
                return false;
            };

$('.calheadclass').on('click', onHeaderClick);
 $('.calbodyclass').on('click', onBodyClick);
      
//disable drag
$this.on('selectstart dragstart', function (e) { e.preventDefault(); });
