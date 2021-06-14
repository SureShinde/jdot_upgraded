define([
    'underscore',
    'Magento_Ui/js/grid/columns/column'
], function (_, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            bodyTmpl: 'RLTSquare_OrderState/ui/grid/cells/text'
        },
        getStatusColor: function (row) {
            if (row.order_engrave == 'ENGRAVING') {
                return '#F00';
            }

        }
    });
});