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
      <el-button
        v-waves
        :loading="downloadLoading"
        class="filter-item"
        type="warning"
        icon="el-icon-download"
        @click="handleDownload"
      >Export</el-button>
    </div>

    

    <el-table
      :key="tableKey"
      v-loading="listLoading"
      :data="list"
      border
      show-summary
      :summary-method="getSummaries"
      fit
      highlight-current-row
      style="width: 100%;"
      @sort-change="sortChange"
      >

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
     

      <el-table-column label="Order No" min-width="110px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.order_no }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Member ID" width="110px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.user.username }}</span>
        </template>
      </el-table-column>
     <!--  <el-table-column label="Package" min-width="130px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.packages?row.packages[0].package.name:'' }}</span>
        </template>
      </el-table-column> -->
      <el-table-column label="Payment Mode" width="140px" align="center">
        <template slot-scope="{row}">
          <el-tag :type="row.payment_mode?row.payment_mode.name:'' | statusFilter">{{ row.payment_mode.name }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="Final Amount" width="110px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.final_amount }}</span>
        </template>
      </el-table-column>
      <el-table-column label="GST" width="130px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.gst }}</span>
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

  </div>
</template>

<script>
import { getAllOrders,} from "@/api/admin/shopping";
import { getStatuesAll } from "@/api/admin/config";
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
      sums:{
        final_total:0,
        gst:0
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
      order:{
        id:undefined,
        delivery_status:undefined,
        delivery_by:undefined,
        tracking_no:undefined,
        remarks:undefined,
      },
      deleveryStatuses:[],
      downloadLoading: false,
      buttonLoading: false,
      dialogOrderDetailsVisible:false, 
      dialogUpdateOrderVisible:false,
      orderTitle:'',
      updateOrderTitle:'',  
      orderRules: {
        delivery_status: [{ required: true, message: 'Order Status is required.', trigger: 'blur' }]
      },  
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
    // getStatuesAll('orders').then(response => {
    //   this.deleveryStatuses = response.data;
    // });
  },
  methods: {
    getSummaries(param) {
      const { columns, data } = param;
      const sums = [];
      columns.forEach((column, index) => {
        if (index === 1) {
          sums[index] = 'Final Total (All)';
          return;
        }
        if(index===5){
          sums[index] = this.sums.final_total;
          return; 
        }
        if(index===6){
          sums[index] = this.sums.gst;
          return; 
        }
      });

      return sums;
    },
    getList() {
      this.listLoading = true;
     
      getAllOrders(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        this.sums=response.sum;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
   
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
    },
    handleDownload() {
      this.downloadLoading = true;
      import("@/vendor/Export2Excel").then(excel => {
        const tHeader = [
          "Sr.No",
          "Order No",
          "Member",
          "Package",
          "Payment Mode",
          "Amount",
          "GST",
          "Created At",
        ];
        const filterVal = [
          "id",
          "order_no",
          "member_id",
          "package",
          "payment_mode",
          "final_amount",
          "gst",
          "created_at",
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "gst"
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === "timestamp") {
            return parseTime(v[j]);
          } else if(j === "member_id") {
            return v.user?v.user.username:''
          }else if(j === "payment_mode") {
            return v.payment_mode?v.payment_mode.name:''
          }else if(j === "package") {
            return v.packages?v.packages[0].package.name:''
          }else {
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
