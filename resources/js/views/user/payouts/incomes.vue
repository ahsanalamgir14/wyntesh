<template>
  <div class="app-container">
    <div class="filter-container">
     
      <el-select v-model="listQuery.income_id" @change="handleFilter" multiple  clearable class="filter-item" style="width:500px;" filterable placeholder="Select Income">
        <el-option
          v-for="item in income_list"
          :key="item.name"
          :label="item.name"
          :value="item.id">
        </el-option>
      </el-select>

      <el-date-picker
        v-model="listQuery.month"
        type="month"
        @change="handleFilter"
        format="yyyy-MM"
        value-format="yyyy-MM"
         class="filter-item"
        placeholder="Pick a month">
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
      
       <el-table-column label="Date" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.payout.sales_end_date | parseTime('{y}-{m}-{d}') }}</span>
        </template>
      </el-table-column>


      <el-table-column label="Income type" min-width="150px">
        <template slot-scope="{row}">
          <span >{{ row.income?row.income.name:'' }}</span>
        </template>
      </el-table-column>

     <!--  <el-table-column label="Sales Start Date" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.payout.sales_start_date | parseTime('{y}-{m}-{d}') }}</span>
        </template>
      </el-table-column> -->

      <el-table-column label="Amount" width="120px" align="right">
        <template slot-scope="{row}">
          <span >{{ Math.round(parseFloat(row.payout_amount)+parseFloat(row.tds)+parseFloat(row.admin_fee)) }}</span>
        </template>
      </el-table-column>
      <el-table-column label="TDS" width="100px" align="right">
        <template slot-scope="{row}">
          <span >{{ Math.round(row.tds) }}</span>
        </template>
      </el-table-column>

      <el-table-column label="Payble Amount" width="130px" align="right">
        <template slot-scope="{row}">
          <span >{{ Math.round(row.payout_amount) }}</span>
        </template>
      </el-table-column>
      <!-- <el-table-column label="Admin Fee" width="130px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.admin_fee }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Income Parameter 1" width="200px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.income_payout_parameter_1_name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Income Parameter 1 Value" width="200px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.income_payout_parameter_1_value }}</span>
        </template>
      </el-table-column> -->

       <el-table-column label="Period date" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.payout.sales_start_date | parseTime('{y}-{m}-{d}') }} - {{ row.payout.sales_end_date | parseTime('{y}-{m}-{d}') }}</span>
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
import { getPayoutIncomes,getAllIncomes} from "@/api/user/payouts";
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
      getPayoutIncomes(this.listQuery).then(response => {
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
    }
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
