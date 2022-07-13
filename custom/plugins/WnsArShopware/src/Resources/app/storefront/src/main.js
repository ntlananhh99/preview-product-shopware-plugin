import ProductPreviewPlugin from './plugins/product-preview-plugin';

// Register plugins via the existing PluginManager
const PluginManager = window.PluginManager;

PluginManager.register('ProductPreviewPlugin', ProductPreviewPlugin, '[data-product-preview-plugin]');
