import Plugin from "src/plugin-system/plugin.class";
import DomAccess from "src/helper/dom-access.helper";
import noUiSlider from './vendor/nouislider.min';
import HttpClient from 'src/service/http-client.service';
import ProductPreviewPlugin from "./product-preview-plugin";

export default class ProductPreviewUploadBg extends Plugin {
    static options = {
        formDataKey: null,
        route: {
            updateBackgroundConfig: {},
            uploadBackground: {},
        },
        ownBackgroundImage: null,
    };

    init() {
        this.file = null;
        this.method = 'upload';

        // Upload modal
        this.inputFile = DomAccess.querySelector(this.el, '.wns-preview-tool-upload-background-input');
        this.backgroundFile = DomAccess.querySelector(this.el, '.wns-preview-tool-upload-background-file-name');
        this.validateBgNameMess = DomAccess.querySelector(this.el, '.wns-preview-tool-upload-background-validate-message')
        this.btnNextToSetting = DomAccess.querySelector(this.el, '.btn-next-to-setting');

        // Setting background image size modal
        this.bgSlider = DomAccess.querySelector(this.el, '.wns-preview-tool-setting-background-image-slider');
        this.imgOutput = DomAccess.querySelector(this.el, '.wns-preview-tool-show-background-image');
        this.settingImageOverlayLeft = DomAccess.querySelector($('#settingBackgroundModal')[0], '.wns-preview-tool-setting-background-image-overlay-left');
        this.settingImageOverlayRight = DomAccess.querySelector($('#settingBackgroundModal')[0], '.wns-preview-tool-setting-background-image-overlay-right');
        this.valueOfSlider = DomAccess.querySelector(this.el, '.range-value');

        // Input value
        this.bgWidthInput = DomAccess.querySelector(this.el, '.bg-width-input');
        this.bgTopInput = DomAccess.querySelector(this.el, '.bg-top-input');
        this.bgLeftInput = DomAccess.querySelector(this.el, '.bg-left-input');

        // Warning when input value empty
        this.empMessBgWidthInput = DomAccess.querySelector(this.el, '.empty-mess-bg-width-input');
        this.empMessBgTopInput = DomAccess.querySelector(this.el, '.empty-mess-bg-top-input');
        this.empMessBgLeftInput = DomAccess.querySelector(this.el, '.empty-mess-bg-left-input');

        // Warning when input value is invalid
        this.invalidMessBgWidthInput = DomAccess.querySelector(this.el, '.invalid-mess-bg-width-input');
        this.invalidMessBgTopInput = DomAccess.querySelector(this.el, '.invalid-mess-bg-top-input');
        this.invalidMessBgLeftInput = DomAccess.querySelector(this.el, '.invalid-mess-bg-left-input');

        this.btnGoBack = DomAccess.querySelector(this.el, '.btn-go-back');
        this.btnSaveChanges = DomAccess.querySelector(this.el, '.btn-save-changes');
        this._httpClient = new HttpClient();

        // Update background
        this.btnShowUpdateModal = document.querySelector('.btn-show-update-modal');

        this.registerEvents();

        const plugin = window.PluginManager.getPluginInstanceFromElement(document.querySelector('[data-product-preview-plugin]'), 'ProductPreviewPlugin');
    }

    registerEvents() {
        // Upload modal
        this.inputFile.addEventListener('change', (event) => this.onFileChange(event), false);
        this.btnNextToSetting.addEventListener('click', (event) => this.onNextToSetting(event), false);

        // Setting background image size modal
        noUiSlider.create(this.bgSlider, {
            start: [25, 75],
            margin: 50,
            padding: [2, 2],
            connect: true,
            range: {
                'min': 0,
                'max': 100
            }
        });
        this.bgSlider.noUiSlider.on('slide', (event) => this.getSliderValue(event), false);
        this.btnGoBack.addEventListener('click', (event) => this.goBack(event), false);
        this.btnSaveChanges.addEventListener('click', (event) => this.onSaveChanges(event), false);

        // Update background
        this.btnShowUpdateModal.addEventListener('click', (event) => this.onShowUpdateModal(event), false);
    }

    onFileChange(event) {
        this.file = event.target.files[0];
        this.backgroundFile.textContent = this.file.name;

        // Check if file selected or not?
        if (this.inputFile.files.length == 0) {

        } else {
            this.bgWidthInput.value = null;
            this.bgTopInput.value = null;
            this.bgLeftInput.value = null;
        }

        // Get file type to validate
        const fileType = this.file['type'];
        const validImageTypes = ['image/jpg', 'image/jpeg', 'image/png', 'image/webp'];
        if (!validImageTypes.includes(fileType)) {
            this.validateBgNameMess.style.display = 'block';
        } else {
            this.validateBgNameMess.style.display = 'none';
            this.btnNextToSetting.style.display = 'block';

            // Setting background image size modal
            this.imgOutput.src = URL.createObjectURL(this.file);
        }
    }

    onNextToSetting(event) {
        event.preventDefault();
        $('#uploadBackgroundModal').modal('hide');
        $('#settingBackgroundModal').modal('show');
    }

    getSliderValue(event) {
        const [left, right] = this.bgSlider.noUiSlider.get();
        this.valueOfSlider.textContent = "[ " + left + " - " + right + " ]";

        this.settingImageOverlayLeft.style.width = left + '%';
        this.settingImageOverlayRight.style.width = 100 - right + '%';
    }

    goBack(event) {
        event.preventDefault();
        $('#uploadBackgroundModal').modal('show');
        $('#settingBackgroundModal').modal('hide');
    }

    onSaveChanges(event) {
        event.preventDefault();

        if (!this.bgWidthInput.value || !this.bgTopInput.value || !this.bgLeftInput.value ||
            isNaN(this.bgWidthInput.value) || isNaN(this.bgTopInput.value) || isNaN(this.bgLeftInput.value)) {
            // Width input
            if (!this.bgWidthInput.value) {
                this.empMessBgWidthInput.style.display = 'block';
                this.invalidMessBgWidthInput.style.display = 'none';
            } else if (isNaN(this.bgWidthInput.value)) {
                this.invalidMessBgWidthInput.style.display = 'block';
                this.empMessBgWidthInput.style.display = 'none';
            }
            else {
                this.empMessBgWidthInput.style.display = 'none';
                this.invalidMessBgWidthInput.style.display = 'none';
            }

            // Top input
            if (!this.bgTopInput.value) {
                this.empMessBgTopInput.style.display = 'block';
                this.invalidMessBgTopInput.style.display = 'none';
            } else if (isNaN(this.bgTopInput.value)) {
                this.invalidMessBgTopInput.style.display = 'block';
                this.empMessBgTopInput.style.display = 'none';
            } else {
                this.empMessBgTopInput.style.display = 'none';
                this.invalidMessBgTopInput.style.display = 'none';
            }

            // Left input
            if (!this.bgLeftInput.value) {
                this.empMessBgLeftInput.style.display = 'block';
                this.invalidMessBgLeftInput.style.display = 'none';
            } else if (isNaN(this.bgLeftInput.value)) {
                this.invalidMessBgLeftInput.style.display = 'block';
                this.empMessBgLeftInput.style.display = 'none';
            } else {
                this.empMessBgLeftInput.style.display = 'none';
                this.invalidMessBgLeftInput.style.display = 'none';
            }

        } else {
            this.empMessBgWidthInput.style.display = 'none';
            this.invalidMessBgWidthInput.style.display = 'none';
            this.empMessBgTopInput.style.display = 'none';
            this.invalidMessBgTopInput.style.display = 'none';
            this.empMessBgLeftInput.style.display = 'none';
            this.invalidMessBgLeftInput.style.display = 'none';

            let formData = new FormData();

            // Check if file selected or not?
            if(this.inputFile.files.length == 0 && this.options.ownBackgroundImage.bgUploadKey) {
                // If no file selected -> Update bg
                console.log("no files selected");
                formData.append('key', this.options.ownBackgroundImage.bgUploadKey);
                this.method = 'update';
            } else {
                // If file selected -> Add new bg
                formData.append('upload_file', this.file);
                this.method = 'upload';
            }

            formData.append('_csrf_token', this.options.route['uploadBackground'].token);
            formData.append('width', this.bgWidthInput.value);
            formData.append('top', this.bgTopInput.value);
            formData.append('left', this.bgLeftInput.value);
            
            this._httpClient.post(
                this.options.route['uploadBackground'].path, formData,
                this._onHandleSaveChangesResponse.bind(this),
                'multipart/form-data'
            );
        }
    }

    _onHandleSaveChangesResponse(res) {
        const response = JSON.parse(res);

        if (response.statusCode == '201' && response.background) {
            $('#settingBackgroundModal').modal('hide');

            this.options.ownBackgroundImage = this.convertDataToViewData(response.background);

            if (this.options.ownBackgroundImage && this.options.ownBackgroundImage.bgUploadKey) {
                this.btnShowUpdateModal.classList.remove('d-none');
            }

            this.setValueForUpdateModal(this.options.ownBackgroundImage);

            document.$emitter.publish('WnsArShopware/onOwnBackgroundImageChanged', {
                background: this.options.ownBackgroundImage,
                method: this.method
            });

        }
    }

    onShowUpdateModal(event) {
        event.preventDefault();
        this.setValueForUpdateModal(this.options.ownBackgroundImage);
    }

    // Set value (width, top, left) when open update modal
    setValueForUpdateModal(background) {
        this.backgroundFile.textContent = background.media.fileName;
        this.btnNextToSetting.style.display = 'block';

        this.bgWidthInput.value = background.bgWidth;
        this.bgTopInput.value = background.topImgPosition;
        this.bgLeftInput.value = background.leftImgPosition;

        this.imgOutput.src = background.media.url;
    }

    convertDataToViewData(data) {
        return {
            id: data.id,
            bgUploadKey: data.bgUploadKey,
            mediaId: data.mediaId,
            bgWidth: data.bgWidth,
            topImgPosition: data.topImgPosition,
            leftImgPosition: data.leftImgPosition,
            metaData: data.media.metaData,
            media: data.media
                ? {
                    fileName: data.media.fileName,
                    url: data.media.url,
                    alt: data.media.alt,
                }
                : null,
        };
    }
}
