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
      <el-table-column label="Actions" align="center" width="100" class-name="small-padding">
        <template slot-scope="{row}">
          <el-button
            type="warning"
            :loading="buttonLoading"
            circle
            icon="el-icon-printer"
            @click="payoutReport(row.id)"
          ></el-button>
        </template>
      </el-table-column>
      <el-table-column label="Payout Duration" min-width="200px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.payout.sales_start_date | parseTime('{y}-{m}-{d}') }} - {{ row.payout.sales_end_date | parseTime('{y}-{m}-{d}') }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Matched BV" width="130px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.total_matched_bv }}</span>
        </template>
      </el-table-column>

      
      <el-table-column label="Rank" width="130px" align="right">
        <template slot-scope="{row}">
          <span >{{ row.rank?row.rank.name:'' }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Amount" width="120px" align="right">
        <template slot-scope="{row}">
          <span >{{ (parseFloat(row.payout_amount)) }}</span>
        </template>
      </el-table-column>
      <el-table-column label="TDS" width="130px" align="right">
        <template slot-scope="{row}">
          <span >{{ (row.tds) }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Total Payable" width="130px" align="right">
        <template slot-scope="{row}">
          <span >{{ (row.net_payable_amount) }}</span>
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
import { fetchPayouts, } from "@/api/user/payouts";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; 

export default {
  name: "Payouts",
  components: { Pagination },
  directives: { waves },
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
      downloadLoading: false,
      buttonLoading: false
    };
  },
  created() {
    this.getList();
  },
  methods: {
    getList() {
      this.listLoading = true;
      fetchPayouts(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    payoutReport(id){
      let routeData = this.$router.resolve({path: '/payout-report/'+id});
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
