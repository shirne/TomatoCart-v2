<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * TomatoCart Open Source Shopping Cart Solution
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License v3 (2007)
 * as published by the Free Software Foundation.
 *
 * @package   TomatoCart
 * @author    TomatoCart Dev Team
 * @copyright Copyright (c) 2009 - 2012, TomatoCart. All rights reserved.
 * @license   http://www.gnu.org/licenses/gpl.html
 * @link    http://tomatocart.com
 * @since   Version 2.0
 * @filesource
 */

// ------------------------------------------------------------------------

  echo 'Ext.namespace("Toc.orders");';
  
  include 'orders_grid.php';
  include 'orders_edit_products_grid.php';
  include 'orders_dialog.php';
  include 'orders_edit_dialog.php';
  include 'orders_products_grid.php';
  include 'orders_status_panel.php';
  include 'orders_edit_panel.php';
  include('orders_choose_shipping_method_dialog.php');
  include 'orders_transaction_grid.php';
  include 'orders_delete_confirm_dialog.php';
?>

Ext.override(Toc.desktop.OrdersWindow, {
  createWindow : function() {
    var desktop = this.app.getDesktop();
    var win = desktop.getWindow('orders-win');
    
    if (!win) {
      grd = Ext.create('Toc.orders.OrdersGrid');
      
      grd.on('delete', function(record) {this.onDeleteOrder(grd, record);}, this);
      grd.on('batchdelete', function(params) {this.onBatchDeleteOrder(grd, params);}, this);
      grd.on('view', this.onViewOrder, this);
      grd.on('notifysuccess', this.onShowNotification, this);
      grd.on('edit', function(record) {this.onEditOrder(grd, record);}, this);
      
      win = desktop.createWindow({
        id: 'orders-win',
        title: '<?php echo lang('heading_orders_title'); ?>',
        width: 850,
        height: 400,
        iconCls: 'icon-orders-win',
        layout: 'fit',
        items: grd
      });
    }
    
    win.show();
  },
  
  onDeleteOrder: function(grd, record) {
    var dlg = this.createOrdersDeleteConfirmDialog(grd, 'delete_order');
    
    dlg.show('delete_order', record.get('orders_id'), record.get('orders_id') + ': ' + record.get('customers_name'));
  },
  
  onBatchDeleteOrder: function(grd, params) {
    var dlg = this.createOrdersDeleteConfirmDialog(grd, 'delete_orders');
    
    dlg.show('delete_orders', Ext.JSON.encode(params.ordersIds), params.orders);
  },
  
  onViewOrder: function(record) {
    var dlg = this.createOrdersDialog({ordersId: record.get("orders_id")});
    
    dlg.setTitle(record.get('orders_id') + ': ' + record.get('customers_name'));
    
    dlg.on('updatesuccess', function(feedback) {
      this.onShowNotification(feedback);
    }, this);
    
    dlg.show();
  },
  
  onEditOrder: function(grd, record) {
   	var dlg = this.createOrdersEditDialog({ordersId: record.get("orders_id")});
   	dlg.setTitle(record.get('orders_id') + ': ' + record.get('customers_name'));
   	
   	dlg.on('saveSuccess', function() {
      grd.onRefresh();
    }, this);
    
    dlg.show();
  },
  
  createOrdersDialog: function(config) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('orders-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow(config, Toc.orders.OrdersDialog);
    }
    
    return dlg;
  },
  
  createOrdersDeleteConfirmDialog: function(grd, action) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('orders-delete-confirm-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({'action': action}, Toc.orders.OrdersDeleteComfirmDialog);
      
      dlg.on('deletesuccess', function(feedback) {
        grd.onRefresh();
        
        this.onShowNotification(feedback);
      }, this);
    }

    return dlg;
  },
  
  createOrdersEditDialog: function(config) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('orders-edit-dialog-win');
    
    if (!dlg) {
      dlg = desktop.createWindow(config, Toc.orders.OrdersEditDialog);
      
      dlg.on('saveSuccess', function(feedback) {
        this.onShowNotification(feedback);
      }, this);
      
      //update shipping / billing address
      dlg.on('updateSuccess', function(feedback) {
        this.onShowNotification(feedback);
      }, this);
      
      //edit shipping method
      dlg.on('editShippingMethod', function(ordersId, grdProducts) {
      	var dlgEditShippingMethod = this.createOrdersChooseShippingMethodDialog(ordersId, grdProducts);
      	
	    dlgEditShippingMethod.show();
      }, this);
      
      //edit orders products
      dlg.on('editProductsSuccess', function(feedback) {
      	this.onShowNotification(feedback);
      }, this);
    }
    
    return dlg;
  },
  
  createOrdersChooseShippingMethodDialog: function (ordersId, grdProducts) {
    var desktop = this.app.getDesktop();
    var dlg = desktop.getWindow('orders-shipping-method-win');
    
    if (!dlg) {
      dlg = desktop.createWindow({ordersId: ordersId}, Toc.orders.OrdersChooseShippingMethodDialog);
      
      dlg.on('saveSuccess', function(feedback) {
      	grdProducts.getStore().load();
      	
        this.app.showNotification({title: TocLanguage.msgSuccessTitle, html: feedback});
      }, this);
    }
    
    return dlg;
  },
  
  onShowNotification: function(feedback) {
    this.app.showNotification({
      title: TocLanguage.msgSuccessTitle,
      html: feedback
    });
  }
});

/* End of file main.php */
/* Location: ./templates/base/web/views/orders/main.php */