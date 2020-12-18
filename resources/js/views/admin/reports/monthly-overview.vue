<template>
  <div class="app-container">
    <div class="filter-container">
      <el-date-picker
        v-model="listQuery.month"
        type="month"
        @change="handleFilter"
        format="yyyy-MM"
        value-format="yyyy-MM"
         class="filter-item"
        placeholder="Pick a month">
      </el-date-picker>
      <!-- <el-button
        v-waves
        :loading="downloadLoading"
        class="filter-item"
        type="warning"
        icon="el-icon-download"
        @click="handleDownload"
      >Export</el-button> -->
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
      
      <el-table-column label="Order Count" width="130px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.order_count }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Order Amount" min-width="150px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.order_total_amount }}</span>
        </template>
      </el-table-column>
      <el-table-column label="BV" width="150px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.order_total_pv }}</span>
        </template>
      </el-table-column>
      <el-table-column label="GST" width="150px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.order_total_gst }}</span>
        </template>
      </el-table-column>
       <el-table-column label="TDS" width="150px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.total_tds }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Admin Fee" width="150px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.total_admin_fee }}</span>
        </template>
      </el-table-column>
     
    </el-table>

  </div>
</template>

<script>
import { getMonthlyOverview, } from "@/api/admin/payouts";
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
        limit: 5,
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
  },
  methods: {
    checkRole,
    getList() {
      this.listLoading = true;
      getMonthlyOverview(this.listQuery).then(response => {
        this.list = response.data;
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
          "Sales start date",
          "Sales end date",
          "Sales BV",
          "Saled Amount",
          "Total Payout",
          "Generated At",
        ];
        const filterVal = [
          "id",
          "sales_start_date",
          "sales_end_date",
          "sales_bv",
          "sales_amount",
          "total_payout",
          "created_at",
        ];
        const data = this.formatJson(filterVal, this.list);
        excel.export_json_to_excel({
          header: tHeader,
          data,
          filename: "payouts"
        });
        this.downloadLoading = false;
      });
    },
    formatJson(filterVal, jsonData) {
      return jsonData.map(v =>
        filterVal.map(j => {
          if (j === "timestamp") {
            return parseTime(v[j]);
          } else if(j === "payout_type") {
            return v.payout_type?v.payout_type.name:''
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
