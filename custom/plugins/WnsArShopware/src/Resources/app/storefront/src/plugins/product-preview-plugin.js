import Plugin from "src/plugin-system/plugin.class";
import DomAccess from "src/helper/dom-access.helper";

export default class ProductPreviewPlugin extends Plugin {
    static options = {
        backgroundImageList: [],
        selectedBackground: null,
        backgroundDropdownItemSelector: '.wns-background-dropdown-item',
        queryParams: {
            productimg_url: null,
            productimg_width: null,
            productimg_height: null,
            productimg_top: null,
            productimg_left: null,
            productimg_id: null,
            backgroundimg_id: null
        },
        route: null,
        dragParameter: {
            shiftX: 0,
            shiftY: 0,
            pageX: 0,
            pageY: 0
        },
        shareUrl: null,
        shareEmail: {
            subject: '',
            contentPlain: '',
            productUrl: ''
        }
    };

    init() {
        this.backgroundDropdownMenu = DomAccess.querySelector(this.el, '.wns-background-dropdown-menu');
        this.productReviewBackground = DomAccess.querySelector(this.el, '.wns-product-preview-background');
        this.backgroundListDropdown = DomAccess.querySelector(this.el, '.wns-background-dropdown-background-list');
        this.draggedImage = DomAccess.querySelector(this.el, '.wns-product-preview-dragged-image');
        this.copyLinkButton = DomAccess.querySelector(this.el, '.wns-copy-btn');
        this.shareLinkDropdown= DomAccess.querySelector(this.el, '.wns-preview-tool-action-share-link')
        this.shareLinkInput = DomAccess.querySelector(this.el, '.wns-preview-tool-share-link-input');
        this.tooltipText = DomAccess.querySelector(this.el, '.tooltiptext');
        this.sendMailButton= DomAccess.querySelector(this.el, '.wns-preview-tool-send-mail-button');

        this.selectedBackground = this.options.queryParams.backgroundimg_id ? this.options.backgroundImageList.find((item) => item.id === this.options.queryParams.backgroundimg_id) : this.options.backgroundImageList[0];
        this.setBackgroundImage(this.selectedBackground.media.url)
        this.setWidthOfProductImage();
        this.setPositionOfProductImage();
        this.registerEvents();

        document.$emitter.subscribe('WnsArShopware/onOwnBackgroundImageChanged', this._onOwnBackgroundImageChanged.bind(this));
    }

    registerEvents() {

        $(this.draggedImage).on(
            'dragstart',
            (event) => this.onDragStart(event)
        );
        $(this.draggedImage).on(
            'mousedown',
            (event) => this.onMouseDown(event)
        );
        $(this.backgroundDropdownMenu).on(
            'click',
            this.backgroundDropdownItemSelector,
            (event) => this.onHandleChangeBackground(event)
        );
        $(this.copyLinkButton).on('click', this.shareLinkDropdown, function (event) {
            event.stopPropagation();
          });
        $(this.copyLinkButton).on(
            'click',
            (event) => this.handleCopyText(event)
        );
    }

    _onOwnBackgroundImageChanged(data) {
        const background = data.detail.background;
        const method = data.detail.method;
        if (background) {
            this.selectedBackground = background;
            this.setBackgroundImage(this.selectedBackground.media.url)

            if (method == 'upload') {
                const linkTag = document.createElement('a');
                const divHoverZoom = document.createElement("div");
                const imgTag = document.createElement("img");

                linkTag.setAttribute('href', '#');
                linkTag.setAttribute('class', 'wns-background-dropdown-item');
                linkTag.setAttribute('data-background-id', background.id);
                divHoverZoom.setAttribute('class', 'img-hover-zoom');
                imgTag.setAttribute('src', background.media.url);
                imgTag.setAttribute('alt', background.media.alt);
                imgTag.setAttribute('class', 'dropdown-item');

                divHoverZoom.appendChild(imgTag);
                linkTag.appendChild(divHoverZoom);

                const ownBackgroundDropdownItem = this.backgroundListDropdown.querySelector('[data-background-id=' + background.id + ']');

                if (ownBackgroundDropdownItem) {
                    ownBackgroundDropdownItem.remove();
                }

                this.backgroundListDropdown.prepend(linkTag);
            }
        }
    }

    onHandleChangeBackground(event) {
        event.preventDefault();
        const dropdownItemSelected = $(event.target).closest(this.options.backgroundDropdownItemSelector).get(0);

        // get data background id
        const dataBackgroundId = dropdownItemSelected.getAttribute('data-background-id');
        this.options.queryParams.backgroundimg_id = dataBackgroundId;
        this.selectedBackground = this.options.backgroundImageList.find((item) => item.id === dataBackgroundId);

        this.setBackgroundImage(this.selectedBackground.media.url)
        this.setWidthOfProductImage();
        this.setPositionOfProductImage();
        this.updateShareUrl();
    }

    setBackgroundImage(bgUrl){
        const image = new Image();
        image.src = bgUrl;

        image.onload = () => {
            $(this.productReviewBackground).css("background-image", "url('" + image.src + "')");
        };
    }
    setWidthOfProductImage() {
        const productimg_width = this.options.queryParams.productimg_width;
        const areaBackgroundWidth = this.selectedBackground.bgWidth;
        const sliderRange = 1 / (10*(75 - 25));
        $(this.draggedImage).css("width", (productimg_width / (areaBackgroundWidth * sliderRange)) + '%');
    }

    setPositionOfProductImage() {
        const query_imgTop = this.options.queryParams.productimg_top
        const query_imgLeft = this.options.queryParams.productimg_left
        const imgTop = this.selectedBackground.topImgPosition;
        const imgLeft = this.selectedBackground.leftImgPosition;
        $(this.draggedImage).css("left", (query_imgLeft ? query_imgLeft : imgLeft) + '%');
        $(this.draggedImage).css("top", (query_imgTop ? query_imgTop : imgTop) + '%');
    }

    onDragStart(event) {
        event.preventDefault();
        return false;
    }
    onMouseDown(event) {
        const onMouseMove = this.onDragOver.bind(this);
        const shiftX=  event.clientX - this.draggedImage.getBoundingClientRect().left;
        const shiftY=  event.clientY - this.draggedImage.getBoundingClientRect().top;

        this.moveAt(event.pageX, event.pageY, shiftX, shiftY);
        document.addEventListener('mousemove', onMouseMove);
        this.draggedImage.onmouseup = () => {
            document.removeEventListener('mousemove', onMouseMove);
            this.draggedImage.onmouseup = null;
            this.onDrop();
        };  
    }

    moveAt(pageX, pageY, shiftX, shiftY) {
        this.dragData = {
            shiftX: shiftX,
            shiftY: shiftY,
            pageX: pageX,
            pageY: pageY
        }
        $(this.draggedImage).css("left", this.dragData.pageX - this.dragData.shiftX + 'px');
        $(this.draggedImage).css("top", this.dragData.pageY - this.dragData.shiftY + 'px');
    }

    onDragOver(event) {
        this.draggedImage.classList.add('moving');
        this.dragData.pageX = event.pageX;
        this.dragData.pageY = event.pageY;
        $(this.draggedImage).css("left", this.dragData.pageX - this.dragData.shiftX + 'px');
        $(this.draggedImage).css("top", this.dragData.pageY - this.dragData.shiftY + 'px');
    }

    onDrop() {
        this.draggedImage.classList.remove('moving');
        this.selectedBackground.topImgPosition = ((this.dragData.pageY - this.dragData.shiftY) * 100) / window.innerHeight;
        this.selectedBackground.leftImgPosition = ((this.dragData.pageX - this.dragData.shiftX) * 100) / window.innerWidth;
        this.options.backgroundImageList.map((bgImg, index) => {
            if (bgImg.id == this.selectedBackground.id) {
                this.options.backgroundImageList[index] = this.selectedBackground;
            }
        })
        this.updateShareUrl();
        this.setPositionOfProductImage();
        this.setWidthOfProductImage();

    }

    updateShareUrl() {
        this.options.queryParams.productimg_top= this.selectedBackground.topImgPosition;
        this.options.queryParams.productimg_left= this.selectedBackground.leftImgPosition;
        const str=[];
        for (let param in this.options.queryParams){
            if(param!= null){
                str.push(encodeURIComponent(param) + '=' + encodeURIComponent(this.options.queryParams[param]));
            }
        }
        this.shareUrl= this.options.route.iframe.path + str.join('&');
        this.shareLinkInput.setAttribute("value", this.shareUrl)
        this.handleSendMail(this.shareUrl);
    }

    handleCopyText(event){
        this.shareLinkInput.select();
        this.shareLinkInput.setSelectionRange(0, 99999); 
	    // window.getSelection().removeAllRanges();
        navigator.clipboard.writeText(this.shareLinkInput.value);
        this.tooltipText.innerHTML = "Copied!";
        setTimeout(() => {
            this.tooltipText.innerHTML = "Copy to clipboard"
        }, 1500);
    }

    handleSendMail(shareLink) {
        const {contentPlain, subject, productUrl} = this.options.shareEmail
        const reg = /{{[\w]*.*frontend.product.preview.*}}/;
        const productUrlReg = /{{[\w]*.*frontend.detail.page.*}}/;
        let newBody = contentPlain.replace(reg, shareLink);
        newBody = newBody.replace(productUrlReg, productUrl);

        this.sendMailButton.href = 'mailto:?' + 'subject=' + encodeURIComponent(subject) + '&body=' + encodeURIComponent(newBody);
    }
}
