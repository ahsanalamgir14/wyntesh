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
      <el-button
        v-waves
        class="filter-item"
        type="primary"
        icon="el-icon-search"
        @click="handleFilter"
      >Search</el-button>
      <el-button
        class="filter-item"
        style="margin-left: 10px;"
        type="success"   
         @click="handleCreate"     
        ><i class="fas fa-plus"></i> Create PIN Request</el-button>      
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
      <el-table-column label="Actions" align="center" width="90" class-name="small-padding">        
        <template slot-scope="{row}">
          <el-tooltip content="Delete" placement="right" effect="dark">
            <el-button
              circle
              type="danger"
              icon="el-icon-delete"
              @click="deleteData(row)"
              ></el-button>
          </el-tooltip>
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
      <el-table-column label="Reference No." width="120px" >
        <template slot-scope="{row}">
          <span>{{ row.reference }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Bank Name" width="150px" >
        <template slot-scope="{row}">
          <span>{{ row.bank.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Payment Status" width="150px" >
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

    <el-dialog :title="dialogTitle" width="60%" top="30px"  :visible.sync="dialogPinRequestVisible">
      <el-form ref="pinRequestForm" :rules="rules" :model="temp"  >
        <el-row :gutter="20">
          <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >
            <el-form-item label="Package" prop="package_id">
              <br>
              <el-select v-model="temp.package_id"  filterable placeholder="Select Package">
                <el-option
                  v-for="item in packages"
                  :key="item.name"
                  :label="item.name"
                  :value="item.id">
                </el-option>
              </el-select>
            </el-form-item>            
            <el-form-item label="Quantity" prop="quantity">
              <el-input type="number" min=0 v-model="temp.quantity" />
            </el-form-item>
            <el-form-item label="Amount" prop="amount">
              <el-input type="number" min=0 v-model="temp.amount" />
            </el-form-item>
          </el-col>
          <el-col  :xs="24" :sm="24" :md="12" :lg="12" :xl="12" >              
            <el-form-item label="Payment Mode" prop="payment_mode">
              <el-select v-model="temp.payment_mode" filterable placeholder="Select Payment Mode">                
                <el-option
                  v-for="item in paymentModes"
                  :key="item.name"
                  :label="item.name"
                  :value="item.id">
                </el-option>
              </el-select>
            </el-form-item>
            <el-form-item label="Payment reference" prop="reference">
              <el-input v-model="temp.reference" />
            </el-form-item>
            <el-form-item label="Bank" prop="bank_id">
              <el-select v-model="temp.bank_id" filterable placeholder="Select Bank">
                <el-option
                  v-for="item in banks"
                  :key="item.name"
                  :label="item.name"
                  :value="item.id">
                </el-option>
              </el-select>
            </el-form-item>

            <el-form-item label="Note" prop="note">
              <el-input
                type="textarea"
                v-model="temp.note"
                :rows="2"
                placeholder="Please Enter note">
              </el-input>
            </el-form-item>
          </el-col>
         
        </el-row>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogPinRequestVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" :loading="buttonLoading" @click="dialogStatus==='create'?createData():updateData()">
          Confirm
        </el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { fetchMyPendingPinRequests, createPinRequest, deletePinRequest } from "@/api/user/pins";
import { getPackages, getPaymentModes, getBankPartners } from "@/api/user/config";

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
        amount:undefined,
        // tax_percentage: undefined,
        // tax_amount: undefined,
        // total_amount:undefined,
        payment_mode:undefined,
        reference:undefined,
        bank_id:undefined,
        note:undefined,
      },

      dialogPinRequestVisible:false,
      dialogStatus: "",
      dialogTitle:'',
      textMap: {
        update: "Edit",
        create: "Create"
      },
      rules: {
        package_id: [{ required: true, message: 'Package is required', trigger: 'blur' }],
        quantity: [{  required: true, message: 'Quantity is required', trigger: 'blur' }],
        amount: [{  required: true, message: 'Amount is required', trigger: 'blur' }],
        // tax_percentage: [{  required: true, message: 'Tax percentage is required', trigger: 'blur' }],
        // tax_amount: [{  required: true, message: 'Tax amount is required', trigger: 'blur' }],
        //total_amount: [{  required: true, message: 'Total amount is required', trigger: 'blur' }],
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
      fetchMyPendingPinRequests(this.listQuery).then(response => {
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
      getBankPartners().then(response => {
        this.banks = response.data;
      });
    },
    resetTemp() {
      this.temp = {
        id:undefined,
        package_id: undefined,
        quantity: undefined,
        amount:undefined,
        // tax_percentage: undefined,
        // tax_amount: undefined,
        // total_amount:undefined,
        payment_mode:undefined,
        reference:undefined,
        bank_id:undefined,
        note:undefined,
      };
    },
    handleCreate() {
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogPinRequestVisible = true;
      this.dialogTitle="Create PIN Request"
      this.$nextTick(() => {
        this.$refs["pinRequestForm"].clearValidate();
      });
    },
    createData() {
      this.buttonLoading=true;
      this.$refs["pinRequestForm"].validate(valid => {
        if (valid) {         
          createPinRequest(this.temp).then((data) => {
            this.list.unshift(data.data);
            this.dialogPinRequestVisible = false;
            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
            this.buttonLoading=false;
            this.resetTemp();
          });
        }
      });
      this.buttonLoading=false;
    },
    deleteData(row) {
      this.$confirm('Are you sure you want to delete PIN Request?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(() => {
        deletePinRequest(row.id).then((data) => {
          this.dialogPinRequestVisible = false;
          this.$notify({
              title: "Success",
              message: data.message,
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
