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
      <el-select v-model="listQuery.income_id" @change="handleFilter"  clearable class="filter-item" style="width:200px;" filterable placeholder="Select Income">
        <el-option
          v-for="item in income_list"
          :key="item.name"
          :label="item.name"
          :value="item.id">
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
      <el-table-column label="Member" width="130px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.member.user.username }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Name" min-width="150px" align="left">
        <template slot-scope="{row}">
          <span >{{ row.member.user.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Income" width="200px">
        <template slot-scope="{row}">
          <span >{{ row.income?row.income.name:'' }}</span>
        </template>
      </el-table-column>
      
      <el-table-column label="Payout Duration" width="200px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.payout.sales_start_date | parseTime('{y}-{m}-{d}') }} - {{ row.payout.sales_end_date | parseTime('{y}-{m}-{d}') }}</span>
        </template>
      </el-table-column> 
      <el-table-column label="Total Payout" width="130px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.payout_amount }}</span>
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
import { getMemberPayoutIncomes,} from "@/api/admin/payouts";
import { getAllIncomes,} from "@/api/admin/incomes";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; 
import role from '@/directive/role'; 
import checkRole from '@/utils/role';

export default {
  name: "GeneratePayout",
  components: { Pagination },
  directives: { waves,role },
  filters: {
    statusFilter(status) {
      const statusMap = {
        1: "success",
        draft: "info",
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
        limit: 15,
        search: undefined,
        sort: "-id"
      },
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        date_range:undefined,
        incomes:undefined
      },
      income_list:[],
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
      dialogPayoutGenerateVisible:false,
      dialogStatus: "",
      dialogTitle:"",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      rules: {
        date_range: [{ required: true, message: 'Date range is required', trigger: 'blur' }],
        incomes: [{ required: true, message: 'Please select income', trigger: 'blur' }],
      },
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getList();
    getAllIncomes().then(response => {
      this.income_list = response.data;
    });
  },
  methods: {
    checkRole,
    getList() {
      this.listLoading = true;
      getMemberPayoutIncomes(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    handleFilter() {
      this.listQuery.page = 1;
      this.getList();
    },
    
    resetTemp() {
      this.temp = {
        id: undefined,
        date_range:undefined,
        incomes:undefined
      };
    },
    handleCreate() {
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogTitle="Generate Payout";
      this.dialogPayoutGenerateVisible = true;
      this.$nextTick(() => {
        this.$refs["payoutGenerateForm"].clearValidate();
      });
    },
    createData() {
      this.$refs["payoutGenerateForm"].validate(valid => {
        if (valid) {
          this.buttonLoading=true;
          generateManualPayout(this.temp).then((data) => {
            this.dialogPayoutGenerateVisible = false;
            this.$notify({
              title: "Success",
              message: data.message,
              type: "success",
              duration: 2000
            });
            this.buttonLoading=false;
            this.resetTemp();
          }).catch((err)=>{
            this.buttonLoading=false;
          });
        }
      });      
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
          "Income",
          "Member",
          "Sales start date",
          "Sales end date",
          "Total Payout",
          "Income parameter",
          "Income parameter value",          
          "Generated At",
        ];
        const filterVal = [
          "id",
          "income",
          "member",
          "sales_start_date",
          "sales_end_date",
          "payout_amount",
          "income_payout_parameter_1_name",
          "income_payout_parameter_1_value",          
          "created_at",
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "member-income-payouts"
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === "timestamp") {
            return parseTime(v[j]);
          } else if(j === "sales_start_date") {
            return v.payout?v.payout.sales_start_date:''
          }else if(j === "sales_end_date") {
            return v.payout?v.payout.sales_end_date:''
          }else if(j === "income") {
            return v.income?v.income.name:''
          }else if(j === "member") {
            return v.member?v.member.user.username:''
          }else {
            return v[j];
          }
        })
      );
    },
  }
};
</script>

<style scoped>
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

</style>
