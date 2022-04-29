$(document).ready(function(){
    $('.alert').hide();
})

function showLoading(){
    $.blockUI({ message: `<div class="lds-roller">
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>`});
}
$('#xmlFrmUpload').submit(function(e){
	e.preventDefault();
	var form = $(this);
    var controllerURL = form.attr('action');
    var formData = new FormData(this);
    $('.alert').hide();
    showLoading();
    $.ajax({
        url: controllerURL,
        type: 'POST',
        data: formData,
        success: function (data) {
            $.unblockUI();
            var r = $.parseJSON(data);
            $('#xmlFrmUpload').trigger('reset');
            if(r.error){
                $('.error-msg').html(r.msg);
                $('.alert-danger').show();
            }else{
                $('#xlsx_download_link').attr('href',r.download_link);
                $('.alert-success').show();
            }
        },
        cache: false,
        contentType: false,
        processData: false
    });
});