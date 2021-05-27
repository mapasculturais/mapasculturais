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