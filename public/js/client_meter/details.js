var client_meter_details = {
    settings: {
        clientMeterId: null,
        ajaxClientMeterReadingUrl: '',
        ajaxClientMeterReadingFormUrl: '',
        ajaxClientMeterPaymentUrl: '',
        ajaxClientMeterPaymentFormUrl: '',
        ajaxClientMeterDetailUrl: ''
    },
    init: function() {
        client_meter_details.initForm();
        client_meter_details.initDataTable();
        client_meter_details.initDetails();
    },
    initDetails: function(){
        $.ajax({
            url: client_meter_details.settings.ajaxClientMeterDetailUrl,
            type: 'GET',
            data: { clientMeterId: client_meter_details.settings.clientMeterId},
            success: function(r){
                if(r.success){
            
                    $("#meterDetail").html(r.html);
                }
            }
        });
    },
    initForm: function(){

        $.each($('.href-modal'), function(){
            var _this = $(this);
            var url = _this.data('type') == 'reading' ? client_meter_details.settings.ajaxClientMeterReadingFormUrl : client_meter_details.settings.ajaxClientMeterPaymentFormUrl;
            
            $(_this).unbind('click').bind('click',function(){
                    
                $('.modal').removeClass('modal-fullscreen');
                
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: { id: _this.data('id'), action: _this.data('action'), clientMeterId: client_meter_details.settings.clientMeterId},
                    beforeSend: function(){
                    $(".modal-content").html('');
                        
                    },
                    success: function(r){
                        if(r.success){
                    
                            $(".modal-content").html(r.html);
                            $('#modal').modal('show');
                        }
                    }
                });
            });
        })
    },
    initDataTable: function() {

        var callBack = function() {
            client_meter_details.initForm();
        };

        client_meter_details.meterDataList = $('#clientMeterReading-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': client_meter_details.settings.ajaxClientMeterReadingUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.clientMeterId=  client_meter_details.settings.clientMeterId;
                }
            },
            'order': [[0, 'desc']],
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': 7 },
                { 'searchable': false, 'targets': 7 }
            ],
            drawCallback: function() {
                callBack();
            },
            responsive: {
                details: {
                    renderer: function( api,rowIdx ) {
                        return global.dataTableResponsiveCallBack(api, rowIdx, callBack);
                    }
                }
            }
        });

        client_meter_details.paymentDataList = $('#clientMeterPayment-datalist').DataTable({
            'processing': true,
            'serverSide': true,
            "lengthChange": false,
            "pageLength": 20,
            'ajax': {
                'url': client_meter_details.settings.ajaxClientMeterPaymentUrl,
                'data': function(d) {
                    d.url = global.settings.url;
                    d.clientMeterId=  client_meter_details.settings.clientMeterId;
                }
            },
            'order': [[0, 'desc']],
            'deferRender': true,
            'columnDefs': [
                { 'orderable': false, 'targets': 5 },
                { 'searchable': false, 'targets': 5 }
            ],
            drawCallback: function() {
                callBack();
            },
            responsive: {
                details: {
                    renderer: function( api,rowIdx ) {
                        return global.dataTableResponsiveCallBack(api, rowIdx, callBack);
                    }
                }
            }
        });

        $('.content-container').removeClass('has-loading');
        $('.content-container-content').removeClass('hide');
    }

};