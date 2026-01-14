<div class="dateSelectorContainer">
    <i class="bx bx-calendar"></i>
    <input type="text" class="form-control fs_7 shadow-none bg-transparent daterange" id="{{ $id }}" name="daterange" placeholder="Select Date Range" autocomplete="off">
</div>

@once
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
@endonce

<script>
    $(document).ready(function() {
        const displayFormat = 'DD MMMM, YYYY';
        const $input = $("#{{ $id }}");

        $input.daterangepicker({
            opens: 'right',
            drops: 'auto',
            locale: {
                format: displayFormat
            },
            startDate: moment(),
            endDate: moment(),
            autoUpdateInput: true,
            maxDate: moment(),
            ranges: {
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
            }
        }, function(start, end, label) {
            if (start.isSame(end, 'day')) {
                $input.val('Today');
            } else {
                $input.val(start.format(displayFormat) + ' - ' + end.format(displayFormat));
            }
        });

        // show today in input by default
        $input.val('Today');

        $input.on('apply.daterangepicker', function(ev, picker) {
            let startDate = picker.startDate.format('YYYY-MM-DD');
            let endDate = picker.endDate.format('YYYY-MM-DD');

            if (picker.startDate.isSame(picker.endDate, 'day')) {
                $(this).val('Today');
            } else {
                $(this).val(picker.startDate.format(displayFormat) + ' - ' + picker.endDate.format(displayFormat));
            }

            let datesData = {
                id: "{{ $id }}",
                start: startDate,
                end: endDate
            };
            dates(datesData);
        });

        $input.on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
        });
    });

// Run Ajax like this in your blade view :)

// function dates(dateInfo) {
//     let id = dateInfo.id;
//     console.log(`ID: ${id}`);

//     if (id == 'missingCertification'){
//         let start = dateInfo.start;
//         let end = dateInfo.end;
//         dutyStatusSummaryReportAjax(start,end)
//         console.log(`ID: ${id}, Start: ${start}, End: ${end}`);
//     }
// }

// ORRRRRRRRRRRRR
// function dates({ id, start, end }) {
//     if (id === 'missingCertification') dutyStatusSummaryReportAjax(start, end);
// }
</script>
