(window.webpackJsonp=window.webpackJsonp||[]).push([["wns-ar-shopware"],{"9iXQ":function(t,e,o){"use strict";(function(t){o.d(e,"a",(function(){return p}));var n=o("FGIj"),i=o("gHbT");function r(t){return(r="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(t){return typeof t}:function(t){return t&&"function"==typeof Symbol&&t.constructor===Symbol&&t!==Symbol.prototype?"symbol":typeof t})(t)}function a(t,e){if(!(t instanceof e))throw new TypeError("Cannot call a class as a function")}function s(t,e){for(var o=0;o<e.length;o++){var n=e[o];n.enumerable=n.enumerable||!1,n.configurable=!0,"value"in n&&(n.writable=!0),Object.defineProperty(t,n.key,n)}}function u(t,e){return!e||"object"!==r(e)&&"function"!=typeof e?function(t){if(void 0===t)throw new ReferenceError("this hasn't been initialised - super() hasn't been called");return t}(t):e}function d(t){return(d=Object.setPrototypeOf?Object.getPrototypeOf:function(t){return t.__proto__||Object.getPrototypeOf(t)})(t)}function c(t,e){return(c=Object.setPrototypeOf||function(t,e){return t.__proto__=e,t})(t,e)}var g,l,h,p=function(e){function o(){return a(this,o),u(this,d(o).apply(this,arguments))}var n,r,g;return function(t,e){if("function"!=typeof e&&null!==e)throw new TypeError("Super expression must either be null or a function");t.prototype=Object.create(e&&e.prototype,{constructor:{value:t,writable:!0,configurable:!0}}),e&&c(t,e)}(o,e),n=o,(r=[{key:"init",value:function(){var t=this;this.backgroundDropdownMenu=i.a.querySelector(this.el,".wns-background-dropdown-menu"),this.productReviewBackground=i.a.querySelector(this.el,".wns-product-preview-background"),this.draggedImage=i.a.querySelector(this.el,".wns-product-preview-dragged-image"),this.copyLinkButton=i.a.querySelector(this.el,".wns-copy-btn"),this.shareLinkDropdown=i.a.querySelector(this.el,".wns-preview-tool-action-share-link"),this.shareLinkInput=i.a.querySelector(this.el,".wns-preview-tool-share-link-input"),this.tooltipText=i.a.querySelector(this.el,".tooltiptext"),this.sendMailButton=i.a.querySelector(this.el,".wns-preview-tool-send-mail-button"),this.selectedBackground=this.options.queryParams.backgroundimg_id?this.options.backgroundImageList.find((function(e){return e.id===t.options.queryParams.backgroundimg_id})):this.options.backgroundImageList[0],this.setBackgroundImage(this.selectedBackground.media.url),this.setWidthOfProductImage(),this.setPositionOfProductImage(),this.registerEvents()}},{key:"registerEvents",value:function(){var e=this;t(this.draggedImage).on("dragstart",(function(t){return e.onDragStart(t)})),t(this.draggedImage).on("mousedown",(function(t){return e.onMouseDown(t)})),t(this.backgroundDropdownMenu).on("click",this.backgroundDropdownItemSelector,(function(t){return e.onHandleChangeBackground(t)})),t(this.copyLinkButton).on("click",this.shareLinkDropdown,(function(t){t.stopPropagation()})),t(this.copyLinkButton).on("click",(function(t){return e.handleCopyText(t)}))}},{key:"onHandleChangeBackground",value:function(e){e.preventDefault();var o=t(e.target).closest(this.options.backgroundDropdownItemSelector).get(0).getAttribute("data-background-id");this.options.queryParams.backgroundimg_id=o,this.selectedBackground=this.options.backgroundImageList.find((function(t){return t.id===o})),this.setBackgroundImage(this.selectedBackground.media.url),this.setWidthOfProductImage(),this.setPositionOfProductImage(),this.updateShareUrl()}},{key:"setBackgroundImage",value:function(e){var o=this,n=new Image;n.src=e,n.onload=function(){t(o.productReviewBackground).css("background-image","url('"+n.src+"')")}}},{key:"setWidthOfProductImage",value:function(){var e=this.options.queryParams.productimg_width,o=this.selectedBackground.bgWidth;t(this.draggedImage).css("width",e/(.002*o)+"%")}},{key:"setPositionOfProductImage",value:function(){var e=this.options.queryParams.productimg_top,o=this.options.queryParams.productimg_left,n=this.selectedBackground.topImgPosition,i=this.selectedBackground.leftImgPosition;t(this.draggedImage).css("left",(o||i)+"%"),t(this.draggedImage).css("top",(e||n)+"%")}},{key:"onDragStart",value:function(t){return t.preventDefault(),!1}},{key:"onMouseDown",value:function(t){var e=this,o=this.onDragOver.bind(this),n=t.clientX-this.draggedImage.getBoundingClientRect().left,i=t.clientY-this.draggedImage.getBoundingClientRect().top;this.moveAt(t.pageX,t.pageY,n,i),document.addEventListener("mousemove",o),this.draggedImage.onmouseup=function(){document.removeEventListener("mousemove",o),e.draggedImage.onmouseup=null,e.onDrop()}}},{key:"moveAt",value:function(e,o,n,i){this.dragData={shiftX:n,shiftY:i,pageX:e,pageY:o},t(this.draggedImage).css("left",this.dragData.pageX-this.dragData.shiftX+"px"),t(this.draggedImage).css("top",this.dragData.pageY-this.dragData.shiftY+"px")}},{key:"onDragOver",value:function(e){this.draggedImage.classList.add("moving"),this.dragData.pageX=e.pageX,this.dragData.pageY=e.pageY,t(this.draggedImage).css("left",this.dragData.pageX-this.dragData.shiftX+"px"),t(this.draggedImage).css("top",this.dragData.pageY-this.dragData.shiftY+"px")}},{key:"onDrop",value:function(){var t=this;this.draggedImage.classList.remove("moving"),this.selectedBackground.topImgPosition=100*(this.dragData.pageY-this.dragData.shiftY)/window.innerHeight,this.selectedBackground.leftImgPosition=100*(this.dragData.pageX-this.dragData.shiftX)/window.innerWidth,this.options.backgroundImageList.map((function(e,o){e.id==t.selectedBackground.id&&(t.options.backgroundImageList[o]=t.selectedBackground)})),this.updateShareUrl(),this.setPositionOfProductImage(),this.setWidthOfProductImage()}},{key:"updateShareUrl",value:function(){this.options.queryParams.productimg_top=this.selectedBackground.topImgPosition,this.options.queryParams.productimg_left=this.selectedBackground.leftImgPosition;var t=[];for(var e in this.options.queryParams)null!=e&&t.push(encodeURIComponent(e)+"="+encodeURIComponent(this.options.queryParams[e]));this.shareUrl=this.options.route.iframe.path+t.join("&"),this.shareLinkInput.setAttribute("value",this.shareUrl),this.handleSendMail(this.shareUrl)}},{key:"handleCopyText",value:function(t){var e=this;this.shareLinkInput.select(),this.shareLinkInput.setSelectionRange(0,99999),navigator.clipboard.writeText(this.shareLinkInput.value),this.tooltipText.innerHTML="Copied!",setTimeout((function(){e.tooltipText.innerHTML="Copy to clipboard"}),1500)}},{key:"handleSendMail",value:function(t){var e=this.options.shareEmail,o=e.contentPlain,n=e.subject,i=e.productUrl,r=o.replace(/{{[\w]*.*frontend.product.preview.*}}/,t);r=r.replace(/{{[\w]*.*frontend.detail.page.*}}/,i),this.sendMailButton.href="mailto:?subject="+encodeURIComponent(n)+"&body="+encodeURIComponent(r)}}])&&s(n.prototype,r),g&&s(n,g),o}(n.a);h={backgroundImageList:[],selectedBackground:null,backgroundDropdownItemSelector:".wns-background-dropdown-item",queryParams:{productimg_url:null,productimg_width:null,productimg_height:null,productimg_top:null,productimg_left:null,productimg_id:null,backgroundimg_id:null},route:null,dragParameter:{shiftX:0,shiftY:0,pageX:0,pageY:0},shareUrl:null,shareEmail:{subject:"",contentPlain:"",productUrl:""}},(l="options")in(g=p)?Object.defineProperty(g,l,{value:h,enumerable:!0,configurable:!0,writable:!0}):g[l]=h}).call(this,o("UoTJ"))},TdEj:function(t,e,o){"use strict";o.r(e);var n=o("9iXQ");window.PluginManager.register("ProductPreviewPlugin",n.a,"[data-product-preview-plugin]")}},[["TdEj","runtime","vendor-node","vendor-shared"]]]);