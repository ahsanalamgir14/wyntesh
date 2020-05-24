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

      <el-select v-model="listQuery.package_id" @change="handleFilter"  clearable class="filter-item" style="width:200px;" filterable placeholder="Select Package">
        <el-option
          v-for="item in packages"
          :key="item.name"
          :label="item.name"
          :value="item.id">
        </el-option>
      </el-select>

      <el-select v-model="listQuery.payment_mode" @change="handleFilter"  clearable class="filter-item" style="width:200px;" filterable placeholder="Select Payment Mode">
        <el-option
          v-for="item in paymentModes"
          :key="item.name"
          :label="item.name"
          :value="item.id">
        </el-option>
      </el-select>

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
      <el-table-column label="Actions" align="center" width="150px" class-name="small-padding">        
        <template slot-scope="{row}">
          <el-tooltip content="Reject" placement="right" effect="dark">
            <el-button
              circle
              type="warning"
              icon="el-icon-close"
              @click="rejectRequest(row)"
              ></el-button>
          </el-tooltip>
          <el-tooltip content="Approve" placement="right" effect="dark">
            <el-button
              circle
              type="success"
              icon="el-icon-check"
              @click="approveRequest(row)"
              ></el-button>
          </el-tooltip>
        </template>
      </el-table-column>
      
      <el-table-column label="Member ID" width="140px" >
        <template slot-scope="{row}">
          <span>{{ row.member.user.username }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Member Name" width="140px" >
        <template slot-scope="{row}">
          <span>{{ row.member.user.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Package" width="120px" >
        <template slot-scope="{row}">
          <span>{{ row.package.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Payment Mode" width="120px" >
        <template slot-scope="{row}">
          <span>{{ row.payment_mode.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Quantity" width="120px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.quantity }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Amount" width="120px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.amount }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Tax %" width="120px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.tax_percentage }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Tax Amount" width="120px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.tax_amount }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Total Amount" width="150px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.total_amount }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Reference No." width="130px" >
        <template slot-scope="{row}">
          <span>{{ row.reference }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Bank Name" width="130px" >
        <template slot-scope="{row}">
          <span>{{ row.bank.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Payment Status" width="130px" >
        <template slot-scope="{row}">
          <span>{{ row.payment_status }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Status" width="150px" >
        <template slot-scope="{row}">
          <el-tag :type="row.status | statusFilter">{{row.status}}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="Note" min-width="150px" >
        <template slot-scope="{row}">
          <span>{{ row.note }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Created at" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.created_at | parseTime('{y}-{m}-{d}') }}</span>
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

    <el-dialog :title="dialogTitle" width="60%" top="30px"  :visible.sync="dialogPinGenerateVisible">
      <el-form ref="pinGenerateForm" :rules="rules" :model="temp"  >
        <el-row :gutter="20">
          <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
            <el-form-item label="Package" prop="package_id">
              <br>
              <el-select disabled v-model="temp.package_id"  filterable placeholder="Select Package">
                <el-option
                  v-for="item in packages"
                  :key="item.name"
                  :label="item.name"
                  :value="item.id">
                </el-option>
              </el-select>
            </el-form-item> 
            <el-form-item label="Member" prop="member">
              <el-input disabled v-model="temp.username" />
            </el-form-item>
            <el-form-item label="Quantity" prop="quantity">
              <el-input type="number" min=0 v-model="temp.quantity" />
            </el-form-item>

            
            
          </el-col>
          <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >              
            <el-form-item label="Base Amount" prop="base_amount">
              <el-input type="number" min=0 v-model="temp.base_amount"  @change="calculateFinalPrice()" />
            </el-form-item>
            <el-form-item label="Tax Percentage" prop="tax_percentage">
              <el-input type="number" min=0 v-model="temp.tax_percentage"  @change="calculateFinalPrice()" />
            </el-form-item>
            <el-form-item label="Tax Amount" prop="tax_amount">
              <el-input type="number" min=0 v-model="temp.tax_amount" />
            </el-form-item>
            <el-form-item label="Net Amount" prop="net_amount">
              <el-input type="number" min=0 v-model="temp.total_amount" />
            </el-form-item>

            <el-form-item label="Note" prop="note">
              <el-input
                type="textarea"
                v-model="temp.note"
                :rows="2"
                placeholder="Please Enter Note">
              </el-input>
            </el-form-item>
          </el-col>
         
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogPinGenerateVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="dialogStatus==='create'?createData():updateData()">
          Approve
        </el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { fetchPendingPinRequests, rejectPinRequest, generatePin } from "@/api/admin/pins";
import { getPackages, getPaymentModes } from "@/api/user/config";

import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination";

export default {
  name: "pending-pin-requests",
  components: { Pagination },
  directives: { waves },
  filters: {
    statusFilter(status) {
      const statusMap = {
        Approved: "success",
        Pending: "info",
        Rejected: "danger"
      };

      return statusMap[status];
    }
  },
  data() {
    return {
      tableKey: 0,
      list: [],
      total: 0,
      listLoading: false,
      listQuery: {
        page: 1,
        limit: 10,
        search:undefined,
        package: 0,
        payment_mode: undefined,
        sort: "+id"
      },
      paymentModes:[],
      packages:[],
      banks:[],
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        id:undefined,
        package_id: undefined,
        quantity: undefined,
        base_amount:undefined,
        tax_percentage: undefined,
        tax_amount: undefined,
        total_amount:undefined,
        payment_mode:undefined,
        reference:undefined,
        bank_id:undefined,
        note:undefined,

        username:undefined,
      },

      dialogPinGenerateVisible:false,
      dialogStatus: "",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      dialogTitle:'',
      rules: {
        package_id: [{ required: true, message: 'Package is required', trigger: 'blur' }],
        quantity: [{  required: true, message: 'Quantity is required', trigger: 'blur' }],
        base_amount: [{  required: true, message: 'Base amount is required', trigger: 'blur' }],
        tax_percentage: [{  required: true, message: 'Tax percentage is required', trigger: 'blur' }],
        tax_amount: [{  required: true, message: 'Tax amount is required', trigger: 'blur' }],
        total_amount: [{  required: true, message: 'Total amount is required', trigger: 'blur' }],
        payment_mode: [{  required: true, message: 'Payment mode is required', trigger: 'blur' }],
        reference: [{  required: true, message: 'Payment reference no is required', trigger: 'blur' }],
      },
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getList();
    this.getConfig();
  },
  methods: {
    
    getList() {
      this.listLoading = true;
      fetchPendingPinRequests(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    getConfig() {
      
      getPackages().then(response => {
        this.packages = response.data;
      });
      getPaymentModes().then(response => {
        this.paymentModes = response.data;
      });
    },
    resetTemp() {
      this.temp = {
        id:undefined,
        package_id: undefined,
        quantity: undefined,
        base_amount:undefined,
        tax_percentage: undefined,
        tax_amount: undefined,
        total_amount:undefined,
        payment_mode:undefined,
        reference:undefined,
        bank_id:undefined,
        note:undefined,

        username:undefined,
      };
    },    
    calculateFinalPrice(){
      if(this.temp.tax_percentage != undefined && this.temp.tax_percentage != null){
        if(this.temp.tax_percentage == 0){
          this.temp.tax_amount=0;
          this.temp.total_amount=this.temp.base_amount;
        }else{
          let gst=(this.temp.tax_percentage*this.temp.base_amount)/100;
          gst=Math.floor(gst);
          this.temp.tax_amount=gst;
          this.temp.total_amount=parseInt(this.temp.base_amount)+gst;
        }        
      }
    },
    approveRequest(row) {
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogTitle = "Generate Pins";
      let temp = Object.assign({}, row);
      this.temp.username=temp.member.user.username;
      this.temp.base_amount=temp.package.base_amount;
      this.temp.tax_percentage=temp.package.gst_rate;
      this.temp.tax_amount=temp.package.gst_amount;
      this.temp.total_amount=temp.package.net_amount;
      this.temp.quantity=temp.quantity;
      this.temp.package_id=temp.package_id;
      this.temp.request_id=temp.id;
      this.temp.owned_by=temp.member.id;

      this.dialogPinGenerateVisible = true;
      this.$nextTick(() => {
        this.$refs["pinGenerateForm"].clearValidate();
      });
    },
    createData() {
      
      this.$refs["pinGenerateForm"].validate(valid => {
        if (valid) {
        this.buttonLoading=true;         
          generatePin(this.temp).then((data) => {
            
            this.dialogPinGenerateVisible = false;
            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
            this.getList();
            this.buttonLoading=false;
            this.resetTemp();
          }).catch((err)=>{
            this.buttonLoading=false;
          });
        }
      });
    },
    rejectRequest(row) {

      this.$prompt('Enter note', 'Confirm', {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
        }).then(({ value }) => {

          let data={
            id:row.id,
            note:value
          };

          rejectPinRequest(data).then((res) => {          
            this.$notify({
                title: "Success",
                message: res.message,
                type: "success",
                duration: 2000
            });
            const index = this.list.indexOf(row);
            this.list.splice(index, 1);
          });

        })
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

<style scoped>

.el-select{
  width:100%;
}
.el-drawer__body {
  padding: 20px;
}
.pagination-container {
  margin-top: 5px;
}
.pagination-container {
  background: #fff;
  padding: 15px 16px;
}
@media (min-width:750px) {
  .img-upload{
    float: right;
    margin-right:20px; 
  }
}
</style>
