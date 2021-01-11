<template>
  <div class="app-container">
    <div class="filter-container">
      <el-select size="mini" v-model="listQuery.delivery_status" @change="handleFilter" clearable class="filter-item mobile_class" style="width:200px;" filterable placeholder="Select Order Status">
        <el-option v-for="item in deleveryStatuses" :key="item.name" :label="item.name" :value="item.name">
        </el-option>
      </el-select>
      <el-date-picker v-model="listQuery.date_range" class="filter-item mobile_class" type="daterange" align="right" unlink-panels size="mini" @change="handleFilter" format="dd-MM-yyyy" value-format="yyyy-MM-dd" range-separator="|" start-placeholder="Start date" end-placeholder="End date">
      </el-date-picker>
    </div>
    <div class="filter-container">
      <el-input v-model="listQuery.search" placeholder="Search Records" size="mini" style="width: 200px;" class="filter-item mobile_class" @keyup.enter.native="handleFilter" />
      <el-button v-waves class="filter-item" type="primary" size="mini" icon="el-icon-search" @click="handleFilter">Search</el-button>
    </div>
    <el-table :key="tableKey" v-loading="listLoading" :data="list" border fit highlight-current-row style="width: 100%;" >
      <el-table-column label="Expand" width="80" type="expand">
        <template slot-scope="{row}">
          <p><b>Delivery Agent</b>: {{ row.delivery_by }}</p>
          <p><b>Remark</b>: {{ row.remarks }}</p>
        </template>
      </el-table-column>
      <el-table-column label="Sr#" prop="id" align="center" type="index" :index="indexMethod" width="70">
      </el-table-column>
      <el-table-column label="Actions" align="center" width="130" class-name="small-padding">
        <template slot-scope="{row}">
          <el-tooltip content="View Order" placement="right" effect="dark">
            <el-button type="primary" :loading="buttonLoading" circle icon="el-icon-view" @click="handleViewOrder(row)"></el-button>
          </el-tooltip>
          <el-tooltip content="View Invoice" placement="right" effect="dark">
            <el-button type="warning" :loading="buttonLoading" circle icon="el-icon-printer" @click="invoice(row.id)"></el-button>
          </el-tooltip>
        </template>
      </el-table-column>
      <el-table-column label="Order No" width="110px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.order_no }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Order Amount" width="130px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.net_amount | convert_with_symbol}}</span>
        </template>
      </el-table-column>
      <el-table-column label="PV" max-width="160px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.pv }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Delivery Status" min-width="140px" align="center">
        <template slot-scope="{row}">
          <el-tag :type="row.delivery_status | statusFilter">{{ row.delivery_status }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="Tracking No" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.tracking_no }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Payment Status" min-width="140px" align="center">
        <template slot-scope="{row}">
          <el-tag :type="row.payment_status | paymentStatusFilter">{{ row.payment_status }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="Payment Mode" min-width="140px" align="center">
        <template slot-scope="{row}">
          <el-tag :type="row.payment_mode?row.payment_mode.name:'' | statusFilter">{{ row.payment_mode.name }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="Created At" width="120px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.created_at | parseTime('{d}-{m}-{y}') }}</span>
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getList" />
    <el-dialog :title="orderTitle" width="90%" :fullscreen="is_mobile" top="2vh" :visible.sync="dialogOrderDetailsVisible">
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
                      <div class="text-gray-700 font-bold text-sm mt-1 ">{{ product.product.name }}</div>
                      <div class="text-gray-500 font-bold text-sm  ">{{product.product.qty}}  {{product.product.qty_unit}}, {{ (product.variant.color?product.variant.color.name:' ') +' '+ (product.variant.size?'('+product.variant.size.brand_size+')':'') }}</div>
                    </div>
                    <div class="quantity">
                      <el-input style="width: 80px;" disabled v-model="product.quantity" />
                    </div>
                    <div class="total-price">{{product.net_amount | convert_with_symbol}}</div>
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
                    <div class="total-price">{{pack.net_amount | convert_with_symbol}}</div>
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
        <!-- <el-tab-pane label="Shipment Details">
          <el-form>
            <el-row :gutter="20">
              <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                <el-form-item label="Full Name" prop="full_name">
                  <el-input disabled v-model="temp.shipping_address.full_name" />
                </el-form-item>
                <el-form-item label="Address" prop="address">
                  <el-input type="textarea" disabled :rows="2" placeholder="Address" v-model="temp.shipping_address.address">
                  </el-input>
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                <el-form-item label="Lanmark" prop="landmark">
                  <el-input disabled v-model="temp.shipping_address.landmark" />
                </el-form-item>
                <el-row :gutter="5">
                  <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                    <el-form-item label="City" prop="city">
                      <el-input disabled v-model="temp.shipping_address.city" />
                    </el-form-item>
                  </el-col>
                  <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                    <el-form-item label="State" prop="state">
                      <el-input disabled v-model="temp.shipping_address.state" />
                    </el-form-item>
                  </el-col>
                </el-row>
                <el-row :gutter="5">
                  <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                    <el-form-item label="Pincode" prop="pincode">
                      <el-input disabled v-model="temp.shipping_address.pincode" />
                    </el-form-item>
                  </el-col>
                  <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                    <el-form-item label="Mobile Number" prop="mobile_number">
                      <el-input disabled v-model="temp.shipping_address.mobile_number" />
                    </el-form-item>
                  </el-col>
                </el-row>
              </el-col>
            </el-row>
          </el-form>
        </el-tab-pane> -->
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
  </div>
</template>
<script>
import { myOrders } from "@/api/user/shopping";
import { getStatuesAll } from "@/api/user/config";
import waves from "@/directive/waves"; 
import { parseTime } from "@/utils";
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
        'Order Cancelled': "danger"
      };
      return statusMap[status];
    },
    paymentStatusFilter(status) {
      const statusMap = {
        'Success': "success",
        'Processing': "warning",
        'Failed': "danger"
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
        delivery_status: undefined,
        sort: "-id",
        date_range: ''
      },
      temp: {
        products: [],
        logs: [],
        shipping_address: {
          full_name: undefined,
        },
      },
      deleveryStatuses: [],
      buttonLoading: false,
      dialogOrderDetailsVisible: false,
      orderTitle: '',
    };
  },
  created() {
    this.getList();
    getStatuesAll('orders').then(response => {
      this.deleveryStatuses = response.data;
    });
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
      myOrders(this.listQuery).then(response => {
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
    invoice(id) {
      let routeData = this.$router.resolve({ path: '/invoice/' + id });
      window.open(routeData.href, '_blank');
    },
    handleFilter() {
      this.listQuery.page = 1;
      this.getList();
    },
  }
};

</script>
<style lang="scss" scoped>

.pagination-container {
  background: #fff;
  padding: 15px 16px;
  margin-top: 5px;
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


/* Product Image */
.image {
  margin-right: 50px;
  margin-top: 5px;
  width: 100px;
  text-align: center;
}

/* Product Description */
.description {
  padding-top: 10px;
  margin-right: 60px;
  width: 250px;
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
