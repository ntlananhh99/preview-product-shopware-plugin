import ProductPreviewPlugin from './plugins/product-preview-plugin';
import ProductPreviewUploadBg from "./plugins/product-preview-upload-bg";

// Register plugins via the existing PluginManager
const PluginManager = window.PluginManager;

PluginManager.register('ProductPreviewPlugin', ProductPreviewPlugin, '[data-product-preview-plugin]');
PluginManager.register('ProductPreviewUploadBg', ProductPreviewUploadBg, '[data-product-preview-upload-bg]');
