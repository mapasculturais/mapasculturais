document.addEventListener('DOMContentLoaded', function(){
    var buttons = document.querySelectorAll('.open-toggle-chat');

    buttons.forEach(function(item){
        item.addEventListener('click', function(event) {
            item.nextSibling.classList.toggle('hidden');
        });
    });
});


tinymce.init({
    selector: '#evaluationEditor',
    language: 'pt_BR',
    plugins: [
        'advlist autolink lists link image charmap',
        'searchreplace fullscreen',
        'insertdatetime help wordcount'
    ],
    menubar: false,
    toolbar: 'undo redo | bold italic backcolor | alignleft aligncenter alignright alignjustify'
});