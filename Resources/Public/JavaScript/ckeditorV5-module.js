import {
				ClassicEditor,
				Essentials,
				Paragraph,
				Bold,
				Italic,
				Font
			} from 'ckeditor5';

            ClassicEditor
                .create( {
					attachTo: document.querySelector( '#description' ),
                    plugins: [ Essentials, Paragraph, Bold, Italic, Font ],
                    toolbar: [
						'undo', 'redo', '|', 'bold', 'italic', '|',
						'selectAll', 'link'
					],
					licenseKey: 'GPL'
				} )
				.then( editor => {
					window.editor = editor;
				} )
				.catch( error => {
					console.error( error );
				} );