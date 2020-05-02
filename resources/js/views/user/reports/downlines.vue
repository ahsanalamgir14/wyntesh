<template>
  <div class="app-container">
   
    <div class="filter-container">
      <el-select v-model="listQuery.is_approved" style="width: 140px" placeholder="Status" class="filter-item" @change="handleFilter">
        <el-option  key="1200" label="All" value="all" />
        <el-option  key="1201" label="Paid" value="1" />
        <el-option  key="1202" label="Unpaid" value="0" />
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
        label="Sr.No"
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
      <el-table-column label="DOJ" width="110px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.doj }}</span>
        </template>
      </el-table-column>
      <el-table-column label="ID" width="160px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.code }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Name" min-width="110px" >
        <template slot-scope="{row}">
          <span >{{ row.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Parent" width="160px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.parent_id }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Sponsor" width="160px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.sponsor_id }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Right" width="160px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.right }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Left" width="160px" align="right">
        <template slot-scope="{row}">
          <span>{{ row.left }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Status" class-name="status-col" width="100">
        <template slot-scope="{row}">
          <el-tag :type="row.status=='Paid'?'sucess':'danger'">{{ row.status }}</el-tag>
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
        <el-form-item label="TDS" label-width="120" prop="tds_amount">
          <el-input  type="number" disabled min=0 v-model="temp.tds_amount" ></el-input>
        </el-form-item>
        <el-form-item label="Final Amount" label-width="120" prop="final_debit">
          <el-input  type="number" disabled min=1 v-model="temp.final_debit" ></el-input>
        </el-form-item>
      </el-form>
      <span slot="footer" class="dialog-footer">
        <el-button @click="dialogWithdrawVisible = false">Cancel</el-button>
        <el-button type="primary" @click="handleWithdraw()">Confirm</el-button>
      </span>
    </el-dialog>

  </div>
</template>

<script>
// import {
//   getMyWithdrawals,
//   getMyBalance,
//   createWithdrawal
// } from "@/api/finances";
import CountTo from 'vue-count-to'
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; // secondary package based on el-pagination
import axios from "axios";

export default {
  name: "Commissions",
  components: { Pagination,CountTo },
  directives: { waves },
  filters: {
    statusFilter(status) {
      const statusMap = {
        1: "success",
        null: "info",
        0: "danger"
      };

      return statusMap[status];
    }
  },
  data() {
    return {
      tableKey: 0,
      list: null,
      total: 0,
      listLoading: true,
      listQuery: {
        page: 1,
        limit: 5,
        is_approved:'all',
        sort: "+id",
        date_range:''
      },
      balance:60000,
      dateRangeFilter:'',
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        id: undefined,
        debit:0,
        tds_amount:0,
        tds_percent:5,
        final_debit:0,
      },
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
  },
  methods: {
    getList() {
      this.listLoading = false;
      this.list=[
        {
          id:1,
          doj:'2020-03-25',
          code:60208647,
          name:100,
          parent_id:73735366,
          sponsor_id:12345678,
          left:25,
          right:10,
          status:'Paid'
        },
        {
          id:2,
          doj:'2020-03-26',
          code:73433452,
          name:100,
          parent_id:12345678,
          sponsor_id:60208648,
          left:10,
          right:30,
          status:'Unpaid'
        },
      ]
      //getMyWithdrawals(this.listQuery).then(response => {
      //   this.list = response.data.data;
      //   this.total = response.data.total;
      //   setTimeout(() => {
      //     this.listLoading = false;
      //   }, 1 * 100);
      // });
      // getMyBalance().then(response => {
      //   this.balance = response.data.balance;
      // });
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
          createWithdrawal(this.temp).then((response) => {
            this.getList();
            this.resetTemp();
            this.dialogWithdrawVisible = false;
            this.$notify({
              title: "Success",
              message: "Withdrawal Created Successfully",
              type: "success",
              duration: 2000
            })

          })
        }
      });
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
    resetTemp() {
      this.temp = {
        id: undefined,
        debit:0,
        tds_amount:0,
        final_debit:0,
      };
    },
    clean(obj) {
      for (var propName in obj) { 
        if (obj[propName] === null || obj[propName] === undefined) {
          delete obj[propName];
        }
      }
    },
    handleDownload() {
      this.downloadLoading = true;
      import("@/vendor/Export2Excel").then(excel => {
        const tHeader = [
          "ID",
          "Amount",
          "TDS",
          "Final amount",
          "Approved",          
          "Remark",
          "Created at",
        ];
        const filterVal = [
          "id",
          "debit",
          "tds_amount",
          "final_debit",
          "is_approved",
          "remark",
          "Created at"
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "withdrawals"
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === "timestamp") {
            return parseTime(v[j]);
          } else {
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
