var $budget = $('#Expense_budget');
var $label = $('#Expense_label');
var $date = $('#Expense_date');

$budget.change(function() {
    var $form = $(this).closest('form');
    var data = {};
    data[$budget.attr('name')] = $budget.val();
    data[$label.attr('name')] = $label.val();
    data[$date.attr('name')] = $date.val();

    $.ajax({
        url : $form.attr('action'),
        type: $form.attr('method'),
        data : data,
        success: function(html, textStatus, jqXHR) {
            $('#Expense_category').replaceWith(
                $(html).find('#Expense_category')
            );
        }
    });
});