<template>
  <div class="app-container">
    <div class="filter-container">
        <el-input
          v-model="listQuery.search"
          placeholder="Search Records"
          style="width: 200px;"
          class="filter-item"
          @keyup.enter.native="handleFilter"
        />
        <el-select v-model="listQuery.delivery_status" @change="handleFilter"  clearable class="filter-item" style="width:200px;" filterable placeholder="Select Order Status">
          <el-option
            v-for="item in deleveryStatuses"
            :key="item.name"
            :label="item.name"
            :value="item.name">
          </el-option>
        </el-select>

      <el-date-picker
        v-model="listQuery.date_range"
        class="filter-item"
        type="daterange"
        align="right"
        unlink-panels
        @change="handleFilter"
        format="yyyy-MM-dd"
        value-format="yyyy-MM-dd"
        range-separator="|"
        start-placeholder="Start date"
        end-placeholder="End date"
        :picker-options="pickerOptions">
      </el-date-picker>
      <el-button
        v-waves
        class="filter-item"
        type="primary"
        icon="el-icon-search"
        @click="handleFilter"
      >Search</el-button>
    </div>

    <el-table
      :key="tableKey"
      v-loading="listLoading"
      :data="list"
      border
      fit
      highlight-current-row
      style="width: 100%;"
      @sort-change="sortChange"
      >
      <el-table-column label="Expand" width="80" type="expand">
        <template slot-scope="{row}">
          <p><b>Amount</b>: {{ row.amount }}</p>
          <p><b>GST</b>: {{ row.gst }}</p>
          <p><b>Shipping Fee</b>: {{ row.shipping_fee }}</p>
          <p><b>Admin Fee</b>: {{ row.admin_fee }}</p>
          <br>
          <p><b>Delivery Agent</b>: {{ row.delivery_by }}</p>
          <p><b>Tracking No</b>: {{ row.tracking_no }}</p>
          <p><b>Remark</b>: {{ row.remarks }}</p>
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
      <el-table-column label="Actions" align="center" width="100" class-name="small-padding">
        <template slot-scope="{row}">
          <el-button
            type="primary"
            :loading="buttonLoading"
            circle
            icon="el-icon-view"
            @click="handleViewOrder(row)"
          ></el-button>
          <el-button
            type="warning"
            :loading="buttonLoading"
            circle
            icon="el-icon-printer"
            @click="invoice(row.id)"
          ></el-button>
        </template>
      </el-table-column>

      <el-table-column label="Order No" width="110px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.order_no }}</span>
        </template>
      </el-table-column>
      <el-table-column label="BV" max-width="160px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.pv }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Delivery Status" min-width="140px"align="center">
        <template slot-scope="{row}">
          <el-tag :type="row.delivery_status | statusFilter">{{ row.delivery_status }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="Month" width="120px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.created_at | parseTime('{y}-{m}') }}</span>
        </template>
      </el-table-column>

      
    </el-table>

    <pagination
      v-show="total>0"
      :total="total"
      :page.sync="listQuery.page"
      :limit.sync="listQuery.limit"
      @pagination="getList"
    />


    <el-dialog :title="orderTitle" width="90%" top="2vh" :visible.sync="dialogOrderDetailsVisible">
      <el-tabs type="border-card">
        <el-tab-pane label="Products">
          <el-form ref="orderForm" style="">
              <el-row :gutter="10" style="margin-top: 20px;">
                <el-col  :xs="24" :sm="24" :md="16" :lg="16" :xl="16" >
                  <div class="shopping-cart">
                    <div class="title">
                      Items
                    </div>
                   
                    <div class="item"  v-for="product in temp.products" :key="product.id">
                      
                      <div class="image" v-lazy-container="{ selector: 'img' }">
                        <img :data-src="product.product.cover_image_thumbnail"  data-loading="images/fallback-product.png" alt="" style="max-height: 50px;max-width: 50px;" />
                      </div>
                   
                      <div class="description">
                        <span>{{product.product.name}}</span>
                      </div>
                   
                      <div class="quantity">
                       
                        <el-input style="width: 80px;" disabled v-model="product.qty"  />
                      </div>
                   
                      <div class="total-price">₹ {{product.final_amount}}</div>
                    </div>
                    <div class="item"  v-for="pack in temp.packages" :key="pack.id">
                      
                      <div class="image" v-lazy-container="{ selector: 'img' }">
                        <img :data-src="pack.package.image"  data-loading="images/fallback-product.png" data-error="images/fallback-product.png" alt="" style="max-height: 50px;max-width: 50px;" />
                      </div>
                   
                      <div class="description">
                        <span>{{pack.package.name}}</span>
                      </div>
                   
                      <div class="quantity">
                       
                        <el-input style="width: 80px;" disabled v-model="pack.qty"  />
                      </div>
                   
                      <div class="total-price">₹ {{pack.final_amount}}</div>
                    </div>
                  </div>
                </el-col>
                <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >
                  <div class="shopping-cart">
                    <div class="title">
                      Order Amount
                    </div>
                    <div class="calculations">
                      <div class="cal-title">
                        <span>Subtotal</span>
                      </div>         
                      <div class="cal-amount"><span>₹ {{temp.amount}}</span></div>
                    </div>
                    <div class="calculations">
                      <div class="cal-title">
                        <span>GST</span>
                      </div>         
                      <div class="cal-amount"><span>₹ {{temp.gst}}</span></div>
                    </div>
                    <div class="calculations">
                      <div class="cal-title">
                        <span>Shipping</span>
                      </div>         
                      <div class="cal-amount"><span>₹ {{temp.shipping_fee}}</span></div>
                    </div>
                    <div class="calculations">
                      <div class="cal-title">
                        <span>Admin Charge</span>
                      </div>         
                      <div class="cal-amount"><span>₹ {{temp.admin_fee}}</span></div>
                    </div>
                    <div class="calculations">
                      <div class="cal-title">
                        <span>Discount</span>
                      </div>         
                      <div class="cal-amount"><span>₹ {{temp.discount}}</span></div>
                    </div>
                    <div class="calculations">
                      <div class="cal-grand">
                        <span>Grand Total</span>
                      </div>         
                      <div class="cal-amount"><span>₹ {{temp.final_amount}}</span></div>
                    </div>
                    <div class="calculations">
                      
                    </div>
                  </div>
                </el-col>
              </el-row>
          </el-form>
        </el-tab-pane>
        <el-tab-pane label="Shipment Details">
          <el-form>
            <el-row :gutter="20">
              <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >                          
                <el-form-item label="Full Name"  prop="full_name">
                  <el-input disabled v-model="temp.shipping_address.full_name" />
                </el-form-item>
                <el-form-item label="Address" prop="address">
                  <el-input
                    type="textarea"
                    disabled
                    :rows="2"
                    placeholder="Address"
                    v-model="temp.shipping_address.address">
                  </el-input>
                </el-form-item>
              </el-col>
              <el-col  :xs="24" :sm="24" :md="8" :lg="8" :xl="8" >                
                <el-form-item label="Lanmark"  prop="landmark">
                  <el-input disabled v-model="temp.shipping_address.landmark" />
                </el-form-item>
                <el-row :gutter="5">
                  <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
                    <el-form-item label="City"  prop="city">
                      <el-input disabled v-model="temp.shipping_address.city" />
                    </el-form-item>
                  </el-col>
                  <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
                    <el-form-item label="State"  prop="state">
                      <el-input disabled v-model="temp.shipping_address.state" />
                    </el-form-item>
                  </el-col>
                </el-row>
                <el-row :gutter="5">
                  <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
                    <el-form-item label="Pincode"  prop="pincode">
                      <el-input disabled v-model="temp.shipping_address.pincode" />
                    </el-form-item>
                  </el-col>
                  <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
                    <el-form-item label="Mobile Number"  prop="mobile_number">
                      <el-input disabled v-model="temp.shipping_address.mobile_number" />
                    </el-form-item>
                  </el-col>
                </el-row>                
              </el-col>
            </el-row>
          </el-form>
        </el-tab-pane>
        <el-tab-pane label="Delivery">
          <el-timeline>
            <el-timeline-item v-for="log in temp.logs" :key="log.id" :timestamp="log.created_at | parseTime('{y}-{m}-{d} {h}:{i}') " placement="top">
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
import waves from "@/directive/waves"; // waves directive
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
      tableKey: 0,
      list: null,
      total: 0,
      listLoading: false,
      listQuery: {
        page: 1,
        limit: 10,
        search:undefined,
        sort: "-id",
        date_range:''
      },
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp:{
        products:[],
        logs:[],
        shipping_address:{},
      },
      deleveryStatuses:[],
      downloadLoading: false,
      buttonLoading: false,
      dialogOrderDetailsVisible:false, 
      orderTitle:'',    
      pickerOptions: {
        shortcuts: [{
          text: 'Last week',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
            picker.$emit('pick', [start, end]);
          }
        }, {
          text: 'Last month',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 30);
            picker.$emit('pick', [start, end]);
          }
        }, {
          text: 'Last 3 months',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 90);
            picker.$emit('pick', [start, end]);
          }
        }]
      },
    };
  },
  created() {
    this.getList();
    getStatuesAll('orders').then(response => {
      this.deleveryStatuses = response.data;
    });
  },
  methods: {
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
    handleViewOrder(row){
      this.dialogOrderDetailsVisible=true;
      this.orderTitle='Order #'+row.order_no;
      this.temp=row;
    },
    invoice(id){
      let routeData = this.$router.resolve({path: '/invoice/'+id});
      window.open(routeData.href, '_blank');
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
      return sort === `+${key}`
        ? "ascending"
        : sort === `-${key}`
        ? "descending"
        : "";
    }
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
  box-shadow: 0px 1px 10px 5px rgba(0,0,0,0.10);
  border-radius: 6px;

  display: flex;
  flex-direction: column;
}
.order-success{
  margin:0 auto;
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
  border-bottom:  1px solid #E1E8EE;
}


.item {
  padding: 0px 15px;
  height: 65px;
  display: flex;
}

.item {
  /*border-top:  1px solid #E1E8EE;*/
  border-bottom:  1px solid #E1E8EE;
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
  0%   { background-position: left;  }
  50%  { background-position: right; }
  100% { background-position: right; }
}

/* Product Image */
.image {
  margin-right: 50px;
  margin-top:5px;
  width: 100px;
  text-align:center;
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

.payment-mode-div  {
    height: 55px;
    border-bottom:  1px solid #E1E8EE;
}
.payment-mode  {
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

.checkout-btn{
  padding: 15px 15px 15px 15px;
}

.make-payment-btn{
  padding: 15px 15px 15px 15px;
  margin:0 auto;
}

.checkout-btn button{
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
  outline:0;
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
