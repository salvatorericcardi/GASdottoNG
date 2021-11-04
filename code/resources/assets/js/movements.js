import utils from "./utils";

class Movements {
    static init(container)
    {
        $('.csv_movement_type_select', container).each((index, item) => {
            this.enforcePaymentMethod($(item));
        }).change((e) => {
            this.enforcePaymentMethod($(e.currentTarget));
        });

        if (container.hasClass('movement-modal')) {
            this.initModals(container);
        }
        else {
            var modals = container.find('.movement-modal');
            if (modals.length > 0) {
                this.initModals(modals);
            }
        }

        $('.movement-type-selector', container).change((e) => {
            var selector = $(e.currentTarget);
            var type = selector.find('option:selected').val();
            var selectors = selector.closest('form').find('.selectors');
            selectors.empty().append(utils.loadingPlaceholder());

            utils.postAjax({
                method: 'GET',
                url: 'movements/create',
                dataType: 'html',
                data: {
                    type: type
                },

                success: function(data) {
                    data = $(data);
                    selectors.empty().append(data);
                    this.j().initElements(data);
                }
            });
        });

        if (container.hasClass('movement-type-editor')) {
            $('select[name=sender_type], select[name=target_type]', container).change(function(e) {
                var editor = $(this).closest('.movement-type-editor');
                var sender = editor.find('select[name=sender_type] option:selected').val();
                var target = editor.find('select[name=target_type] option:selected').val();
                var table = editor.find('table');

                table.find('tbody tr').each(function() {
                    var type = $(this).attr('data-target-class');
                    /*
                        Le righe relative al GAS non vengono mai nascoste, in quanto
                        molti tipi di movimento vanno ad incidere sui saldi globali
                        anche quando il GAS non è direttamente coinvolto
                    */
                    $(this).toggleClass('hidden', (type != 'App\\Gas' && type != sender && type != target));
                });

                table.find('thead input[data-active-for]').each(function() {
                    var type = $(this).attr('data-active-for');
                    if(type != '' && type != sender && type != target)
                        $(this).prop('checked', false).prop('disabled', true).change();
                    else
                        $(this).prop('disabled', false);
                });
            });

            $('table thead input:checkbox', container).change(function() {
                var active = $(this).prop('checked');
                var index = $(this).closest('th').index();

                if (active == false) {
                    $(this).closest('table').find('tbody tr').each(function() {
                        var cell = $(this).find('td:nth-child(' + (index + 1) + ')');
                        cell.find('input[value=ignore]').click();
                        cell.find('label, input').prop('disabled', true);
                    });
                }
                else {
                    $(this).closest('table').find('tbody tr').each(function() {
                        $(this).find('td:nth-child(' + (index + 1) + ')').find('label, input').prop('disabled', false);
                    });
                }
            });
        }
    }

    static initModals(container)
    {
        $('input[name=method]', container).change(function() {
            if ($(this).prop('checked') == false) {
                return;
            }

            var method_string = 'when-method-' + $(this).val();
            $(this).closest('.movement-modal').find('[class*="when-method-"]').each(function() {
                $(this).toggleClass('hidden', ($(this).hasClass(method_string) == false));
            });
        });

        $('input[name=amount]', container).change(function() {
            var status = $(this).closest('.movement-modal').find('.sender-credit-status');
            if (status.length) {
                var amount = utils.parseFloatC($(this).val());
                var current = utils.parseFloatC(status.find('.current-sender-credit').text());

                if (amount > current) {
                    status.removeClass('alert-success').addClass('alert-danger');
                }
                else {
                    status.removeClass('alert-danger').addClass('alert-success');
                }
            }
        });
    }

    /*
        Questa è per forzare i metodi di pagamento disponibili nel modale di
        importazione dei movimenti contabili
    */
    static enforcePaymentMethod(node)
    {
        var selected = node.find('option:selected').val();
        var default_payment = null;
        var payments = null;

        JSON.parse(node.closest('.modal').find('input[name=matching_methods_for_movement_types]').val()).forEach(function(iter) {
            if (iter.method == selected) {
                default_payment = iter.default_payment;
                payments = iter.payments;
                return false;
            }
        });

        if (payments != null) {
            node.closest('tr').find('.csv_movement_method_select').find('option').each(function() {
                var v = $(this).val();
                if (payments.indexOf(v) >= 0) {
                    $(this).prop('disabled', false);

                    if (default_payment == v) {
                        $(this).prop('selected', true);
                    }
                }
                else {
                    $(this).prop('disabled', true);
                }
            });
        }
        else {
            node.closest('tr').find('.csv_movement_method_select').find('option').prop('disabled', false);
        }
    }
}

export default Movements;
