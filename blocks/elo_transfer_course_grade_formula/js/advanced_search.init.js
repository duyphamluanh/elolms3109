$(function () {
    var config = {
        '.chosen-select': {width: '100%'},
        '.chosen-select-tagsinput': {width: '100%',max_selected_options: 10}
    }
    for (var selector in config) {
        $(selector).chosen(config[selector]);
    }
    $(".chosen-select-tagsinput").bind("chosen:maxselected", function () {
        alert("Bạn chỉ được chọn tối đa 4 lớp!!!");
    });

    // $(document).ready(function () {
        // $("select#crformat").change(function () {
        //     if ($('#btneloreports').length)
        //         $("#btneloreports").attr('title', $(this).find('option:selected').text());
        //     else
        //         $("#btneloreportsview").attr('title', $(this).find('option:selected').text());
        // });
    // });
});