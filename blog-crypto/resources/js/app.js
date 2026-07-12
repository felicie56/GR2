import './bootstrap';

import Alpine from 'alpinejs';

import {
    Autoformat,
    BlockQuote,
    Bold,
    ClassicEditor,
    Essentials,
    Heading,
    Image,
    ImageCaption,
    ImageStyle,
    ImageTextAlternative,
    ImageToolbar,
    ImageUpload,
    Italic,
    Link,
    LinkImage,
    List,
    Paragraph,
    PasteFromOffice,
    Strikethrough,
    Underline,
} from 'ckeditor5';

import vietnameseTranslations from 'ckeditor5/translations/vi.js';
import 'ckeditor5/ckeditor5.css';

window.Alpine = Alpine;
Alpine.start();

/**
 * Upload adapter riêng cho Laravel.
 *
 * CKEditor gọi adapter này khi người dùng:
 * - bấm nút upload ảnh;
 * - kéo thả ảnh vào editor;
 * - dán ảnh từ clipboard.
 */
class LaravelImageUploadAdapter {
    constructor(loader, options) {
        this.loader = loader;
        this.uploadUrl = options.uploadUrl;
        this.csrfToken = options.csrfToken;
        this.abortController = null;
    }

    upload() {
        return this.loader.file.then((file) => {
            this.abortController = new AbortController();

            const formData = new FormData();
            formData.append('upload', file);

            return fetch(this.uploadUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: formData,
                signal: this.abortController.signal,
                credentials: 'same-origin',
            })
                .then(async (response) => {
                    const payload = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        const validationMessage =
                            payload?.errors?.upload?.[0];

                        throw new Error(
                            validationMessage
                            || payload?.message
                            || 'Không thể tải ảnh lên máy chủ.'
                        );
                    }

                    if (!payload.url) {
                        throw new Error(
                            'Máy chủ đã nhận ảnh nhưng không trả về URL hợp lệ.'
                        );
                    }

                    return {
                        default: payload.url,
                    };
                })
                .catch((error) => {
                    if (error.name === 'AbortError') {
                        throw new Error('Quá trình tải ảnh đã bị hủy.');
                    }

                    throw error;
                });
        });
    }

    abort() {
        this.abortController?.abort();
    }
}

function LaravelImageUploadAdapterPlugin(editor) {
    const sourceElement = editor.sourceElement;
    const uploadUrl = sourceElement?.dataset?.uploadUrl;

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute('content');

    editor.plugins
        .get('FileRepository')
        .createUploadAdapter = (loader) => {
            if (!uploadUrl) {
                throw new Error(
                    'Textarea CKEditor chưa có thuộc tính data-upload-url.'
                );
            }

            return new LaravelImageUploadAdapter(loader, {
                uploadUrl,
                csrfToken: csrfToken || '',
            });
        };
}

const richTextEditorConfig = {
    licenseKey: 'GPL',

    plugins: [
        Essentials,
        Paragraph,
        Heading,
        Autoformat,
        Bold,
        Italic,
        Underline,
        Strikethrough,
        Link,
        List,
        BlockQuote,
        PasteFromOffice,
        Image,
        ImageUpload,
        ImageCaption,
        ImageStyle,
        ImageToolbar,
        ImageTextAlternative,
        LinkImage,
    ],

    extraPlugins: [LaravelImageUploadAdapterPlugin],

    toolbar: {
        items: [
            'undo',
            'redo',
            '|',
            'heading',
            '|',
            'bold',
            'italic',
            'underline',
            'strikethrough',
            'link',
            '|',
            'bulletedList',
            'numberedList',
            'blockQuote',
            '|',
            'uploadImage',
        ],
        shouldNotGroupWhenFull: true,
    },

    heading: {
        options: [
            {
                model: 'paragraph',
                title: 'Đoạn văn',
                class: 'ck-heading_paragraph',
            },
            {
                model: 'heading2',
                view: 'h2',
                title: 'Đề mục lớn',
                class: 'ck-heading_heading2',
            },
            {
                model: 'heading3',
                view: 'h3',
                title: 'Đề mục nhỏ',
                class: 'ck-heading_heading3',
            },
            {
                model: 'heading4',
                view: 'h4',
                title: 'Đề mục phụ',
                class: 'ck-heading_heading4',
            },
        ],
    },

    image: {
        toolbar: [
            'imageStyle:inline',
            'imageStyle:block',
            'imageStyle:side',
            '|',
            'toggleImageCaption',
            'imageTextAlternative',
            '|',
            'linkImage',
        ],
    },

    link: {
        addTargetToExternalLinks: true,
        defaultProtocol: 'https://',
    },

    language: 'vi',
    translations: [vietnameseTranslations],
};

async function initializeRichTextEditor(textarea) {
    if (textarea.dataset.editorInitialized === 'true') {
        return;
    }

    textarea.dataset.editorInitialized = 'true';

    try {
        const editor = await ClassicEditor.create(
            textarea,
            richTextEditorConfig
        );

        textarea._ckeditorInstance = editor;

        const form = textarea.closest('form');

        if (form) {
            form.addEventListener('submit', () => {
                textarea.value = editor.getData();
            });
        }
    } catch (error) {
        textarea.dataset.editorInitialized = 'false';

        console.error(
            'Không thể khởi tạo CKEditor:',
            error
        );
    }
}

function initializeAllRichTextEditors() {
    document
        .querySelectorAll('textarea[data-rich-text-editor]')
        .forEach((textarea) => {
            initializeRichTextEditor(textarea);
        });
}

if (document.readyState === 'loading') {
    document.addEventListener(
        'DOMContentLoaded',
        initializeAllRichTextEditors
    );
} else {
    initializeAllRichTextEditors();
}