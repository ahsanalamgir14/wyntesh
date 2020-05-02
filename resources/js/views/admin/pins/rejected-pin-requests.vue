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
  </div>
</template>

<script>
import { fetchMyRejectedPinRequests, createPinRequest, deletePinRequest } from "@/api/user/pins";
import { getPackages, getPaymentModes, getBankPartners } from "@/api/user/config";

import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination";

export default {
  name: "pin-requests",
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
        limit: 5,
        search:undefined,
        package: 0,
        approved: undefined,
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

      dialogStatus: "",
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
      fetchMyRejectedPinRequests(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    sortChange(data) {
      const { prop, order } = data;
      if (prop === "id") {
        this.sortByID(order);
      }
    },
    handleFilter() {
      this.listQuery.page = 1;
      this.getList();
    },
    sortblur(data) {
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
