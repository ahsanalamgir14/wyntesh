<template>
  <div class="app-container">
    <div class="filter-container">
      <el-date-picker v-model="listQuery.date_range" class="filter-item mobile_class" type="daterange" align="right" unlink-panels @change="handleFilter" format="dd-MM-yyyy" value-format="yyyy-MM-dd" range-separator="|" start-placeholder="Start date" end-placeholder="End date" size="mini">
      </el-date-picker>
    </div>
    <div class="filter-container">
      <el-input v-model="listQuery.search" placeholder="Search Records" style="width: 200px;" size="mini" class="filter-item mobile_class" @keyup.enter.native="handleFilter" />
      <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter" size="mini">Search</el-button>
      <el-button v-waves :loading="downloadLoading" class="filter-item" type="warning" icon="el-icon-download" @click="handleDownload" size="mini">Export</el-button>
    </div>
    <el-table :key="tableKey" v-loading="listLoading" :data="list" border fit highlight-current-row style="width: 100%;" @sort-change="sortChange">
      <el-table-column label="Expand" width="80" type="expand">
        <template slot-scope="{row}">
          <p><b>Amount</b>: {{ row.net_amount }}</p>
          <p><b>IGST</b>: {{ row.gst_amount }}</p>
          <p><b>CGST</b>: {{ row.cgst_amount }}</p>
          <p><b>SGST</b>: {{ row.sgst_amount }}</p>
          <p><b>Shipping Fee</b>: {{ row.shipping_fee }}</p>
          <p><b>Remark</b>: {{ row.remarks }}</p>
          <p><b>Created at</b>: {{ row.created_at | parseTime('{d}-{m}-{y}') }}</p>
        </template>
      </el-table-column>
        <el-table-column
        label="ID"
        prop="id"
        sortable="custom"
        align="center"
        width="80"
        :class-name="getSortClass('id')"
      >
        <template slot-scope="{row}">
          <span>{{ row.id }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Actions" align="center" width="140" class-name="small-padding">
        <template slot-scope="{row}">
          <el-button type="primary" :loading="buttonLoading" circle icon="el-icon-view" @click="handleViewOrder(row)"></el-button>
          <el-button type="warning" :loading="buttonLoading" circle icon="el-icon-edit" @click="handleUpdateOrder(row)"></el-button>
        </template>
      </el-table-column>
      <el-table-column label="Order No" width="110px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.order_no }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Member ID" width="110px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.user.username }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Order Amount" width="130px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.net_amount | parse_currency }}</span>
        </template>
      </el-table-column>
      <el-table-column label="PV" max-width="160px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.pv }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Delivery Status" min-width="140px" align="center">
        <template slot-scope="{row}">
          <el-tag :type="row.delivery_status | statusFilter">{{ row.delivery_status }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="Payment Mode" min-width="140px" align="center">
        <template slot-scope="{row}">
          <el-tag :type="row.payment_mode?row.payment_mode.name:'' | statusFilter">{{ row.payment_mode.name }}</el-tag>
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getList" />
    <el-dialog :title="orderTitle" :fullscreen="is_mobile" width="90%" top="2vh" :visible.sync="dialogOrderDetailsVisible">
      <el-tabs type="border-card">
        <el-tab-pane label="Products">
          <el-form ref="orderForm" style="">
            <el-row :gutter="10" style="margin-top: 20px;">
              <el-col :xs="24" :sm="24" :md="16" :lg="16" :xl="16">
                <div class="shopping-cart">
                  <div class="title">
                    Items
                  </div>
                  <div class="item" v-for="product in temp.products" :key="product.id">
                    <div class="image" v-lazy-container="{ selector: 'img' }">
                      <img :data-src="product.product.cover_image_thumbnail" data-loading="images/fallback-product.png" alt="" style="max-height: 50px;max-width: 50px;" />
                    </div>
                   <div class="description">
                      <div class="text-gray-700 font-bold text-xs mt-1 ">{{ product.product.name }}</div>
                      <div class="text-gray-500 font-bold text-sm  ">{{product.product.qty}}  {{product.product.qty_unit}}, {{ (product.variant.color?product.variant.color.name:' ') +' '+ (product.variant.size?'('+product.variant.size.brand_size+')':'') }}</div>
                    </div>
                    <div class="quantity">
                      <el-input style="width: 80px;" disabled v-model="product.quantity" />
                    </div>
                    <div class="total-price"> {{product.net_amount | parse_currency }}</div>
                  </div>
                  <div class="item" v-for="pack in temp.packages" :key="pack.id">
                    <div class="image" v-lazy-container="{ selector: 'img' }">
                      <img :data-src="pack.product.cover_image_thumbnail" data-loading="images/fallback-product.png" alt="" style="max-height: 50px;max-width: 50px;" />
                    </div>
                    <div class="description">
                      <div class="text-gray-700 font-bold text-sm mt-1 ">{{ pack.product.name }}, {{pack.package.name}}</div>
                      <div class="text-gray-500 font-bold text-sm  ">{{pack.product.qty}} {{pack.product.qty_unit}}, {{ (pack.variant.color?pack.variant.color.name:' ') +' '+ (pack.variant.size?'('+pack.variant.size.brand_size+')':'') }} </div>
                        
                    </div>
                    <div class="quantity">
                      <el-input style="width: 80px;" disabled v-model="pack.quantity" />
                    </div>
                    <div class="total-price"> {{pack.net_amount | parse_currency }}</div>
                  </div>
                </div>
              </el-col>
              <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                <div class="shopping-cart">
                  <div class="title">
                    Order Amount
                  </div>
                  <div class="calculations">
                    <div class="cal-title">
                      <span>Subtotal</span>
                    </div>
                    <div class="cal-amount"><span>{{temp.base_amount | convert_with_symbol}}</span></div>
                  </div>
                  <div class="calculations">
                    <div class="cal-title">
                      <span>IGST</span>
                    </div>
                    <div class="cal-amount"><span>{{temp.gst_amount | convert_with_symbol}}</span></div>
                  </div>
                  <div class="calculations">
                    <div class="cal-title">
                      <span>SGST</span>
                    </div>
                    <div class="cal-amount"><span>{{temp.sgst_amount | convert_with_symbol}}</span></div>
                  </div>
                  <div class="calculations">
                    <div class="cal-title">
                      <span>CGST</span>
                    </div>
                    <div class="cal-amount"><span>{{temp.cgst_amount | convert_with_symbol}}</span></div>
                  </div>
                  <div class="calculations">
                    <div class="cal-title">
                      <span>Shipping</span>
                    </div>
                    <div class="cal-amount"><span>{{temp.shipping_fee | convert_with_symbol}}</span></div>
                  </div>
                  <div class="calculations">
                    <div class="cal-title">
                      <span>Discount</span>
                    </div>
                    <div class="cal-amount"><span>{{temp.distributor_discount | convert_with_symbol}}</span></div>
                  </div>
                  <div class="calculations">
                    <div class="cal-grand">
                      <span>Grand Total</span>
                    </div>
                    <div class="cal-amount"><span>{{temp.net_amount | convert_with_symbol}}</span></div>
                  </div>
                  <div class="calculations">
                  </div>
                </div>
                <div class="shopping-cart pb-4" >
                  <div class="title" >
                    Shipping Address
                  </div>
                  <div class="p-4">
                    {{temp.shipping_address}}
                  </div>
                </div>
              </el-col>
            </el-row>
          </el-form>
        </el-tab-pane>
      
        <el-tab-pane label="Delivery">
          <el-timeline>
            <el-timeline-item v-for="log in temp.logs" :key="log.id" :timestamp="log.created_at | parseTime('{d}-{m}-{y} {h}:{i}') " placement="top">
              <el-card>
                <h4>{{ log.delivery_status }}</h4>
                <p>{{ log.remarks }}</p>
              </el-card>
            </el-timeline-item>
          </el-timeline>
        </el-tab-pane>
      </el-tabs>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogOrderDetailsVisible = false">
          Cancel
        </el-button>
      </div>
    </el-dialog>
    <el-dialog :title="updateOrderTitle" :fullscreen="is_mobile" width="40%" center :visible.sync="dialogUpdateOrderVisible" style="height: auto;margin: 0 auto;">
      <el-form ref="orderUpdateForm" :rules="orderRules" :model="order" style="width: 70%;margin: 0 auto;">
        <el-form-item label="Order Status" prop="delivery_status">
          <br>
          <el-select v-model="order.delivery_status" clearable style="width:100%;" filterable placeholder="Order Status">
            <el-option v-for="item in deleveryStatuses" :key="item.name" :label="item.name" :value="item.name">
            </el-option>
          </el-select>
        </el-form-item>
        <el-form-item label="Delivery Agent" prop="delivery_by">
          <el-input v-model="order.delivery_by" />
        </el-form-item>
        <el-form-item label="Tracking No" prop="tracking_no">
          <el-input v-model="order.tracking_no" />
        </el-form-item>
        <el-input type="textarea" v-model="order.remarks" :rows="2" placeholder="Please Enter Note">
        </el-input>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogUpdateOrderVisible = false">Cancel</el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="updateOrder()">Update</el-button>
      </span>
    </el-dialog>
  </div>
</template>
<script>
import { getNewOrders, updateOrder } from "@/api/admin/shopping";
import waves from "@/directive/waves"; 
import { parseTime } from "@/utils";
import { parse_currency } from "@/utils/currencies";
import Pagination from "@/components/Pagination";

export default {
  name: "orders",
  components: { Pagination },
  directives: { waves },
  filters: {
    statusFilter(status) {
      const statusMap = {
        'Order Created': "info",
        'Order Confirmed': "success",
        'Order Prepared': "warning",
        'Order Dispached': "warning",
        'Order Delivered': "success",
        'Order Cancelled': "danger",
        'Order Returned': "danger"
      };
      return statusMap[status];
    }
  },
  data() {
    return {
      is_mobile: false,
      tableKey: 0,
      list: null,
      total: 0,
      listLoading: false,
      listQuery: {
        page: 1,
        limit: 10,
        search: undefined,
        sort: "-id",
        date_range: ''
      },
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        products: [],
        logs: [],
        shipping_address: {},
      },
      order: {
        id: undefined,
        delivery_status: undefined,
        delivery_by: undefined,
        tracking_no: undefined,
        remarks: undefined,
      },
      orderRules: {
        delivery_status: [{ required: true, message: 'Order Status is required.', trigger: 'blur' }]
      },
      deleveryStatuses: [{
          name: 'Order Created',
        },
        {
          name: 'Order Confirmed',
        },
        {
          name: 'Order Prepared',
        },
        {
          name: 'Order Dispached',
        },
        {
          name: 'Order Delivered',
        },
        {
          name: 'Order Cancelled',
        }
      ],
      downloadLoading: false,
      buttonLoading: false,
      dialogOrderDetailsVisible: false,
      dialogUpdateOrderVisible: false,
      orderTitle: '',
      updateOrderTitle: '',
    };
  },
  created() {
    this.getList();
    if (window.screen.width <= '550') {
      this.is_mobile = true;
    }
  },
  methods: {
    indexMethod(index) {
      let page = this.listQuery.page;
      if (this.listQuery.page == 1) {
        let tempIndex = index * 1;
        let total = this.total + 1;
        return total - (tempIndex + 1);
      } else {

        let tempIndex = this.listQuery.limit * (page - 1) + index;;
        let total = this.total + 1;
        return total - (tempIndex + 1);
      }
    },
    getList() {
      this.listLoading = true;
      getNewOrders(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    handleViewOrder(row) {
      this.dialogOrderDetailsVisible = true;
      this.orderTitle = 'Order #' + row.order_no;
      this.temp = row;
      if (!this.temp.shipping_address) {
        this.temp.shipping_address = {
          full_name: undefined,
        };
      }
    },
    handleUpdateOrder(row) {
      let row_data = Object.assign({}, row);
      this.order.id = row_data.id;
      this.order.delivery_status = row.delivery_status;
      this.order.delivery_by = row.delivery_by;
      this.order.tracking_no = row.tracking_no;
      this.dialogUpdateOrderVisible = true;
      this.updateOrderTitle = 'Update Order #' + row_data.order_no;
    },
    updateOrder() {
      this.$refs["orderUpdateForm"].validate(valid => {
        if (valid) {
          this.buttonLoading = true;
          updateOrder(this.order).then((response) => {
            this.getList();
            this.dialogUpdateOrderVisible = false;
            this.buttonLoading = false;
            this.resetOrder();
            this.$notify({
              title: "Success",
              message: response.message,
              type: "success",
              duration: 2000
            });
          }).catch((err) => {
            this.buttonLoading = false;
          });
        }
      });
    },
    resetOrder() {
      this.order = {
        id: undefined,
        delivery_status: undefined,
        delivery_by: undefined,
        tracking_no: undefined,
        remarks: undefined,
      };
    },
    handleFilter() {
      this.listQuery.page = 1;
      this.getList();
    },
    sortChange(data) {
      const { prop, order } = data;
      if (prop === "id") {
        this.sortByID(order);
      }
    },
    sortByID(order) {
      if (order === "ascending") {
        this.listQuery.sort = "+id";
      } else {
        this.listQuery.sort = "-id";
      }
      this.handleFilter();
    },

    getSortClass: function(key) {
      const sort = this.listQuery.sort;
      return sort === `+${key}` ?
        "ascending" :
        sort === `-${key}` ?
        "descending" :
        "";
    },
    handleDownload() {
      this.downloadLoading = true;
      import("@/vendor/Export2Excel").then(excel => {
        const tHeader = [
          "Sr.No",
          "Order No",
          "Member",
          "Product Code",
          "Product Name",
          "Variant",
          "Quantity",
          "Product Price",
          "Order Amount",
          "GST",
          "CGST",
          "SGST",
          "UTGST",
          "Shipping Fee",
          "Admin Charge",
          "Discount",
          "Final Amount",
          "Created At",
        ];
        const filterVal = [
          "id",
          "order_no",
          "member_id",
          "product_number",
          "product_name",
          "variant",
          "product_quantity",
          "product_price",
          "base_amount",
          "gst_amount",
          "cgst_amount",
          "sgst_amount",
          "utgst_amount",
          "shipping_fee",
          "admin_fee",
          "discount",
          "net_amount",
          "created_at",
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "orders"
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      let orders = [];
      jsonData.forEach((v) => {
        v.products.forEach((p) => {
          let ad = v.shipping_address;
          let order = {
            id: v.id,
            order_no: v.order_no,
            member_id: v.user.username,
            product_number: p.product.product_number,
            product_name: p.product.name,
            variant: (p.variant.color?p.variant.color.name:'')+' - '+ (p.variant.size?p.variant.size.brand_size:''),
            product_quantity: p.quantity,
            product_price: p.net_amount,
            base_amount: v.base_amount,
            gst_amount: v.gst_amount,
            cgst_amount: v.cgst_amount,
            sgst_amount: v.sgst_amount,
            utgst_amount: v.utgst_amount,
            shipping_fee: v.shipping_fee,
            admin_fee: v.admin_fee,
            discount: v.discount,
            net_amount: v.net_amount,

            created_at: v.created_at,
          };
          orders.push(order);
        });
        v.packages.forEach((p) => {
          let ad = v.shipping_address;
          let order = {
            id: v.id,
            order_no: v.order_no,
            member_id: v.user.username,
            product_number: p.package.code,
            product_name: p.package.name,
            variant: '',
            product_quantity: p.quantity,
            product_price: p.net_amount,
            base_amount: v.base_amount,
            gst_amount: v.gst_amount,
            cgst_amount: v.cgst_amount,
            sgst_amount: v.sgst_amount,
            utgst_amount: v.utgst_amount,
            shipping_fee: v.shipping_fee,
            admin_fee: v.admin_fee,
            discount: v.discount,
            net_amount: v.net_amount,
            created_at: v.created_at,
          };
          orders.push(order);
        });

      });

      return orders.map(v =>
        filterVal.map(j => {
          if (j === "created_at") {
            return parseTime(v[j]);
          } else {
            return v[j];
          }
        })
      );
    },
  }
};

</script>
<style lang="scss" scoped>
.pagination-container {
  margin-top: 5px;
}

.pagination-container {
  background: #fff;
  padding: 15px 16px;
}

.cancel-btn {
  position: absolute;
  right: 15px;
  top: 10px;
}

* {
  box-sizing: border-box;
}

html,
body {
  width: 100%;
  height: 100%;
  padding: 10px;
  margin: 0;
  background-color: #7EC855;
  font-family: 'Roboto', sans-serif;
}

.shopping-cart {

  background: #FFFFFF;
  box-shadow: 0px 1px 10px 5px rgba(0, 0, 0, 0.10);
  border-radius: 6px;

  display: flex;
  flex-direction: column;
}

.order-success {
  margin: 0 auto;
  width: 50%;
}

.title {
  height: 60px;
  border-bottom: 1px solid #E1E8EE;
  padding: 20px 30px;
  color: #5E6977;
  font-size: 18px;
  font-weight: 400;
}

.calculations {
  padding: 5px 7px;
  height: 40px;
  display: flex;
}

.calculations {
  /* border-top:  1px solid #E1E8EE;*/
  border-bottom: 1px solid #E1E8EE;
}


.item {
  padding: 0px 15px;
  height: 65px;
  display: flex;
}

.item {
  /*border-top:  1px solid #E1E8EE;*/
  border-bottom: 1px solid #E1E8EE;
}

/* Buttons -  Delete and Like */
.buttons {
  position: relative;
  padding-top: 12px;
  margin-right: 60px;
}

.delete-btn {
  display: inline-block;
  cursor: pointer;
  width: 18px;
  height: 17px;

  margin-right: 20px;
}

.like-btn {
  position: absolute;
  top: 9px;
  left: 15px;
  display: inline-block;

  width: 60px;
  height: 60px;
  background-size: 2900%;
  background-repeat: no-repeat;
  cursor: pointer;
}

.is-active {
  animation-name: animate;
  animation-duration: .8s;
  animation-iteration-count: 1;
  animation-timing-function: steps(28);
  animation-fill-mode: forwards;
}

@keyframes animate {
  0% {
    background-position: left;
  }

  50% {
    background-position: right;
  }

  100% {
    background-position: right;
  }
}

/* Product Image */
.image {
  margin-right: 10px;
  margin-top: 5px;
  width: 100px;
  text-align: center;
}

/* Product Description */
.description {
  padding-top: 10px;
  margin-right: 20px;
  width: 300px;
}

.description span {
  display: block;
  font-size: 14px;
  color: #43484D;
  font-weight: 400;
}

.description span:first-child {
  margin-bottom: 5px;
}

.description span:last-child {
  font-weight: 300;
  margin-top: 8px;
  color: #86939E;
}

.cal-title {
  width: 100%;
}

.cal-title span {
  margin-left: 25px;
  display: block;
  font-size: 16px;
  color: #6c7175;
  font-weight: 400;
}

.cal-grand {
  width: 100%;
}

.cal-grand span {
  margin-left: 25px;
  display: block;
  font-size: 16px;
  margin-top: 5px;
  color: #5d5d5d;
  font-weight: 400;
}

.payment-mode-div {
  height: 55px;
  border-bottom: 1px solid #E1E8EE;
}

.payment-mode {
  margin: 10px 20px 10px 20px;
}

.cal-title span:first-child {
  margin-bottom: 5px;
}

.cal-title span:last-child {
  font-weight: 300;
  margin-top: 8px;
  color: #86939E;
}

/* Product Quantity */
.quantity {
  padding-top: 10px;
  margin-right: 60px;
}

.quantity input {
  -webkit-appearance: none;
  border: none;
  text-align: center;
  width: 32px;
  font-size: 16px;
  color: #43484D;
  font-weight: 300;
}

.checkout-btn {
  padding: 15px 15px 15px 15px;
}

.make-payment-btn {
  padding: 15px 15px 15px 15px;
  margin: 0 auto;
}

.checkout-btn button {
  float: right;
}


button[class*=btn] {
  width: 30px;
  height: 30px;
  background-color: #E1E8EE;
  border-radius: 6px;
  border: none;
  cursor: pointer;
}

.minus-btn img {
  margin-bottom: 3px;
}

.plus-btn img {
  margin-top: 2px;
}

button:focus,
input:focus {
  outline: 0;
}

/* Total Price */
.total-price {
  width: 83px;
  padding-top: 20px;
  text-align: center;
  font-size: 16px;
  color: #43484D;
  font-weight: 300;
}

.cal-amount {
  width: 100%;
  margin-right: 25px;
  padding-top: 8px;
  text-align: right;
  font-size: 16px;
  color: #43484D;
  font-weight: 300;
}

/* Responsive */
@media (max-width: 800px) {
  .shopping-cart {
    width: 100%;
    height: auto;
    overflow: hidden;
  }

  .item {
    height: auto;
    flex-wrap: wrap;
    justify-content: center;
  }

  .image img {
    max-height: 50px;
    max-width: 50px;
  }

  .image,
  .quantity,
  .description {
    width: 100%;
    text-align: center;
    margin: 6px 0;
  }

  .buttons {
    margin-right: 20px;
  }
}

</style>
