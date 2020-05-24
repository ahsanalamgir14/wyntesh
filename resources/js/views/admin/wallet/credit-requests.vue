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

      <el-select v-model="listQuery.status" style="width: 140px" clearable placeholder="Status" class="filter-item" @change="handleFilter">
        <el-option label="Pending" value="Pending" />
        <el-option label="Approved" value="Approved" />
        <el-option label="Rejected" value="Rejected" />
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
        <template slot-scope="{row}" >
          <el-tooltip content="Reject" placement="right" effect="dark" v-if="row.status=='Pending'">
            <el-button
              circle
              type="warning"
              icon="el-icon-close"
              :loading="buttonLoading"
              @click="rejectRequest(row)"
              ></el-button>
          </el-tooltip>
          <el-tooltip content="Approve" placement="right" effect="dark" v-if="row.status=='Pending'">
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
      <el-table-column label="Payment Mode" width="120px" >
        <template slot-scope="{row}">
          <span>{{ row.payment_mode.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Amount" width="120px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.amount }}</span>
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

    <el-dialog :title="dialogTitle" width="40%" top="30px"  :visible.sync="dialogAddWalletCreditisible">
      <el-form ref="addWalletCreditForm" :rules="rules" :model="temp"  >
        <el-row :gutter="20">
          <el-col  :xs="24" :sm="24" :md="24" :lg="24" :xl="24" >
            
            <el-form-item label="Member" prop="member">
              <el-input disabled v-model="temp.username" />
            </el-form-item>
            <el-form-item label="Amount" prop="amount">
              <el-input disabled type="number" min=0 v-model="temp.amount" />
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
        <el-button @click="dialogAddWalletCreditisible = false">
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
import { fetchCreditRequests, rejectCreditRequest, approveCreditRequest } from "@/api/admin/wallet";
import {  getPaymentModes } from "@/api/user/config";

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
        payment_mode: undefined,
        status:'Pending',
        sort: "+id"
      },
      paymentModes:[],
      banks:[],
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        id:undefined,
        amount:undefined,
        payment_mode:undefined,
        reference:undefined,
        bank_id:undefined,
        note:undefined,
        username:undefined,
      },

      dialogAddWalletCreditisible:false,
      dialogStatus: "",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      dialogTitle:'',
      rules: {
        amount: [{  required: true, message: 'Amount is required', trigger: 'blur' }],      
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
      fetchCreditRequests(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      }).catch((er)=>{
        this.listLoading = false;
      });
    },
    getConfig() {      
      getPaymentModes().then(response => {
        this.paymentModes = response.data;
      });
    },
    resetTemp() {
      this.temp = {
        id:undefined,
        amount:undefined,
        payment_mode:undefined,
        reference:undefined,
        bank_id:undefined,
        note:undefined,

        username:undefined,
      };
    }, 
    approveRequest(row) {
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogTitle = "Approve Credit Request";
      let temp = Object.assign({}, row);
      this.temp.username=temp.member.user.username;
      this.temp.request_id=temp.id;
      this.temp.amount=temp.amount;
      this.temp.requested_by=temp.requested_by;

      this.dialogAddWalletCreditisible = true;
      this.$nextTick(() => {
        this.$refs["addWalletCreditForm"].clearValidate();
      });
    },
    createData() {
      
      this.$refs["addWalletCreditForm"].validate(valid => {
        if (valid) {       
          this.buttonLoading=true;
          approveCreditRequest(this.temp).then((data) => {
            
            this.dialogAddWalletCreditisible = false;
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

      this.$prompt('Enter note', 'Confirm Reject', {
          confirmButtonText: 'OK',
          cancelButtonText: 'Cancel',
        }).then(({ value }) => {

          let data={
            id:row.id,
            note:value
          };
          this.buttonLoading=true;
          rejectCreditRequest(data).then((res) => {          
            this.$notify({
                title: "Success",
                message: res.message,
                type: "success",
                duration: 2000
            });
            this.buttonLoading=false;
            this.getList();
          }).catch((err)=>{
            this.buttonLoading=false;
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
