<template>
  <div class="app-container">
    <el-alert
        v-if="is_active"
        style="margin-bottom: 10px;"
        :title="title"
        type="error"
        :description="msg"
        show-icon>
      </el-alert>

    <el-row :gutter="40" class="panel-group">
      <el-col :xs="12" :sm="12" :lg="6" class="card-panel-col">
        <div class="card-panel" >
          <div class="card-panel-icon-wrapper icon-message">
            <i class="fas fa-wallet card-panel-icon" style="color: #27AE60;" ></i>
          </div>
          <div class="card-panel-description">            
            <count-to :start-val="0" :end-val="balance" :duration="3000" class="card-panel-num" />
            <div class="card-panel-text">
              Wallet Balance
            </div>
          </div>
        </div>
      </el-col>
    </el-row>
    <div class="filter-container">

      <el-button
        v-waves
        :loading="downloadLoading"
        class="filter-item"
        type="warning"
        icon="el-icon-plus"
        @click="$router.push('/wallet/credit-requests')"
      >Credit Request</el-button>
      <el-button
        v-waves
        :loading="downloadLoading"
        class="filter-item"
        type="warning"
        disabled
        icon="el-icon-plus"
      >Add Balance Online</el-button>
      <br>
      <el-select v-model="listQuery.status" @change="handleFilter"  clearable class="filter-item" style="width:200px;" filterable placeholder="Select Status">
        <el-option
          v-for="item in statuses"
          :key="item.name"
          :label="item.name"
          :value="item.name">
        </el-option>
      </el-select>

      <el-date-picker
        v-model="dateRangeFilter"
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
        type="primary"
        icon="el-icon-download"
        @click="handleDownload"
      >Export</el-button>
      <el-button
        v-waves
        :disabled="!kyc_status"
        :loading="downloadLoading"
        class="filter-item"
        type="success"
        icon="el-icon-upload"
        @click="checkPV"
      >{{ kyc_status?'Withdraw':'Verify your KYC First to Withdraw'}}</el-button>
        <!-- dialogWithdrawVisible=true -->
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
      <el-table-column label="Actions" align="center" width="170px" class-name="small-padding">        
        <template slot-scope="{row}">         
          <el-tooltip content="Delete" placement="right" effect="dark" v-if="row.request_status=='Pending'">
            <el-button
              circle
              type="danger"
              icon="el-icon-delete"
              @click="deleteRequest(row)"
              ></el-button>
          </el-tooltip>
        </template>
      </el-table-column> 
      <el-table-column label="Amout" width="110px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.amount }}</span>
        </template>
      </el-table-column>
      <!-- <el-table-column label="Expected TDS" width="160px" align="right">
        <template slot-scope="{row}">
          <span>{{ (parseFloat(row.amount)*parseFloat(temp.tds_percent))/100 }}</span>
        </template>
      </el-table-column> -->
      <el-table-column label="Approved?" class-name="status-col" width="120">
        <template slot-scope="{row}">
          <el-tag :type="row.request_status | statusFilter">{{ row.request_status }}</el-tag>
        </template>
      </el-table-column>
      <el-table-column label="Created At" width="120px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.created_at | parseTime('{y}-{m}-{d}') }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Note" min-width="150px"align="left">
        <template slot-scope="{row}">
          <span >{{ row.note }}</span>
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

    <el-dialog title="Withdraw" width="40%"  :visible.sync="dialogWithdrawVisible">
      <el-form ref="formWithdraw" :rules="rules" :model="temp">
        <el-form-item label="Amount to withdraw" prop="debit" label-width="120">
          <el-input  type="number" min=1 @change="handleDebitChange" v-model="temp.debit" ></el-input>
        </el-form-item>
        <el-form-item label="Expected TDS" label-width="120" prop="tds_amount">
          <el-input  type="number" disabled min=0 v-model="temp.tds_amount" ></el-input>
        </el-form-item>
        <el-form-item label="Final Amount you will receive" label-width="120" prop="final_debit">
          <el-input  type="number" disabled min=1 v-model="temp.final_debit" ></el-input>
        </el-form-item>
        <el-form-item label="Note" prop="note">
          <el-input
            type="textarea"
            v-model="temp.note"
            :rows="2"
            placeholder="Please Enter note">
          </el-input>
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogWithdrawVisible = false">Cancel</el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="handleWithdraw()">Withdraw</el-button>
      </span>
    </el-dialog>

  </div>
</template>

<script>
import {
  fetchWithdrawalRequests,
  deleteWithdrawalRequest,
  createWithdrawalRequest
} from "@/api/user/wallet";
import { getSettings } from "@/api/user/settings";
import { getStatuesAll } from "@/api/user/config";

import CountTo from 'vue-count-to'
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; 

export default {
  name: "WithdrawalRequests",
  components: { Pagination,CountTo },
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
        is_active : 0,
        title : undefined,
        tableKey: 0,
        list: null,
        total: 0,
        listLoading: true,
        listQuery: {
            page: 1,
            limit: 5,
            status:undefined,
            sort: "+id",
            date_range:''
        },
        balance:0,
        minimum_withdrawal:0,
        msg: undefined,
        kyc_status:0,
        total_personal_pv:0,
        dateRangeFilter:'',
        sortOptions: [
            { label: "ID Ascending", key: "+id" },
            { label: "ID Descending", key: "-id" }
        ],
        temp: {
            id: undefined,
            debit:0,
            tds_amount:0,
            tds_percent:0,
            final_debit:0,
            note:undefined,
        },
        statuses:[],
        dialogWithdrawVisible:false,
        dialogStatus: "",
        textMap: {
            update: "Edit",
            create: "Create"
        },
        rules: {
            debit: [
              { type:"number", required: true, message: "Enter amount", trigger: "blur" }
            ],
            final_debit: [
              { type:"number", required: true, message: "Final Amount cannot be zero", trigger: "blur" }
            ],
        },
        settings:undefined,
        downloadLoading: false,
        buttonLoading: false,
        pickerOptions: {
        shortcuts: [{
          text: 'Last week',
          onClick(picker) {
            const end = new Date();
            const start = new Date();
            start.setTime(start.getTime() - 3600 * 1000 * 24 * 7);
            picker.$emit('pick', [start, end]);
          }
        }, 
        {
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
    this.getSettings();
  },
  methods: {
    getList() {
      this.listLoading = true;     
      fetchWithdrawalRequests(this.listQuery).then(response => {
        this.list               = response.data.data;
        this.total              = response.data.total;
        this.balance            = parseFloat(response.balance);
        this.minimum_withdrawal = parseInt(response.minimum_withdrawal);
        this.kyc_status         = response.kyc_status;
        this.total_personal_pv         = response.total_personal_pv;
        // this.kyc_status         = true; // remove
        this.setMsg();
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      }).catch((er)=>{
        this.listLoading = false;
      });
    },
    setMsg() {     
        this.msg ="Your self purchase must be "+this.minimum_withdrawal+", to withdraw your earnings."; 
    }, 
    getSettings() {      
      getSettings().then(response => {
        this.settings = response.data
        //this.temp.tds_percent=parseFloat(response.data.tds_percentage);
      });
      getStatuesAll('withdrawal_requests').then(response => {
        this.statuses = response.data;
      });
    },
    handleDebitChange(){
      let tds=(this.temp.debit*this.temp.tds_percent)/100
      this.temp.tds_amount=parseFloat(tds);
      this.temp.debit=parseFloat(this.temp.debit);
      this.temp.final_debit=(parseFloat(this.temp.debit, 10)-parseFloat(this.temp.tds_amount, 10));
    },
    handleWithdraw(){
      this.$refs["formWithdraw"].validate(valid => {
        if(parseFloat(this.temp.final_debit) <=0 ){
          this.$message.error('Withdrawal amount cannot be 0.');
          return;
        }
        if(parseFloat(this.balance) < parseFloat(this.temp.debit)){
          this.$message.error('Oops, You have not enough balance to withdraw.');
          return;
        }
        if (valid) {
           this.buttonLoading=true;
          createWithdrawalRequest(this.temp).then((response) => {
            this.getList();
            this.resetTemp();
            this.dialogWithdrawVisible = false;
             this.buttonLoading=false;
            this.$notify({
              title: "Success",
              message: "Withdrawal Created Successfully",
              type: "success",
              duration: 2000
            })

          }).catch((err)=>{
            this.buttonLoading=false;
          });
        }
      });
    },
    deleteRequest(row){
      this.$confirm('Are you sure you want to delete withdrawal Request?', 'Warning', {
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        type: 'warning'
      }).then(() => {
        deleteWithdrawalRequest(row.id).then((data) => {
          this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
          });
          this.getList();
        });
      })
    },
    resetTemp() {
      this.temp = {
        id: undefined,
        debit:0,
        tds_amount:0,
        final_debit:0,
        note:undefined,
      };
      this.temp.tds_percent=this.settings.tds_percentage;
    },
    handleFilter() {
      this.listQuery.page = 1;
      this.listQuery.date_range=this.dateRangeFilter;
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
    clean(obj) {
      for (var propName in obj) { 
        if (obj[propName] === null || obj[propName] === undefined) {
          delete obj[propName];
        }
      }
    },
    checkPV() {
        if(this.total_personal_pv>=this.minimum_withdrawal){
            this.dialogWithdrawVisible=true;
            this.is_active = 0;
        }else{
            this.is_active = 1;
            this.dialogWithdrawVisible=false;
        }
    },
    handleDownload() {
      this.downloadLoading = true;
      import("@/vendor/Export2Excel").then(excel => {
        const tHeader = [
          "ID",
          "Amount",
          "Status",
          "Created at",
        ];
        const filterVal = [
          "id",
          "amount",
          "request_status",
          "created_at"
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "WithdrawalRequests"
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === "timestamp") {
            return parseTime(v[j]);
          } else if(j=='member'){
            return v.member.user.username
          }else {
            return v[j];
          }
        })
      );
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
.edit-input {
  padding-right: 100px;
}
.cancel-btn {
  position: absolute;
  right: 15px;
  top: 10px;
}

.panel-group {
  margin-top: 18px;

  .card-panel-col {
    margin-bottom: 32px;
  }

  .card-panel {
    height: 108px;
    cursor: pointer;
    font-size: 12px;
    position: relative;
    overflow: hidden;
    color: #666;
    background: #fff;
    box-shadow: 4px 4px 40px rgba(0, 0, 0, .05);

    .card-panel-icon-wrapper {
      float: left;
      margin: 10px 0 0 10px;
      padding: 5px;
      transition: all 0.38s ease-out;
      border-radius: 6px;
    }

    .card-panel-icon {
      float: left;
      font-size: 35px;
      border-style: solid;
      border-width: thin;
      padding:5px;
      height: 35px;
      width: 45px;
    }
    @media (min-width:550px) {
      .card-panel-description {
        float: right;
        font-weight: bold;
        margin-top: 15px;
        margin-right: 10px;
        width: 90%;

        .card-panel-text {
          line-height: 25px;
          color: rgba(0, 0, 0, 0.45);
          font-size: 14px;
          display: block;
          margin-top: 5px;
        }

        .card-panel-num {
          font-size: 25px;
          float:right;
          display: block;
        }
      }
    }
  }
}

@media (max-width:550px) {
  .card-panel{
    .card-panel-description {
        font-weight: bold;
          margin: 5px auto !important;
          float: none !important;
          text-align: center;
        .card-panel-text {
          line-height: 20px;
          color: rgba(0, 0, 0, 0.45);
          font-size: 10px;
        }

        .card-panel-num {
          display: block;
          font-size: 20px;
        
        }
      }
  }

  .card-panel-icon-wrapper {
    float: none !important;
    margin: 0 !important;

    svg {
      display: block;
      margin: 5px auto !important;
      float: none !important;
    }
  }
}

</style>
