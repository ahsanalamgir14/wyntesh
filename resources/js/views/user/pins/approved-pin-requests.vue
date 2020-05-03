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
      <el-table-column label="Actions" align="center" width="150px" class-name="small-padding">        
        <template slot-scope="{row}">
          <el-tooltip content="View Pins" placement="right" effect="dark">
            <el-button
              circle
              type="success"
              icon="el-icon-view"
              @click="viewRequestPins(row)"
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

    <el-dialog :title="dialogTitle" width="80%" top="30px"  :visible.sync="dialogViewPinsVisible">
      <div class="filter-container">
    
        <el-input
          v-model="pinsListQuery.search"
          placeholder="Search Records"
          style="width: 200px;"
          class="filter-item"
          @keyup.enter.native="handlePinsFilter"
        />

        <el-select v-model="pinsListQuery.status" @change="handlePinsFilter"  clearable class="filter-item" style="width:200px;" filterable placeholder="Select Status">
          <el-option label="Used" value="Used"></el-option>
          <el-option label="Not Used" value="Not Used"></el-option>
        </el-select>


        <el-button
          v-waves
          class="filter-item"
          type="primary"
          icon="el-icon-search"
          @click="handlePinsFilter"
        >Search</el-button>
             
      </div>
      <el-table
        :key="pinsTableKey"
        v-loading="listLoading"
        :data="pinsList"
        border
        fit
        highlight-current-row
        style="width: 100%;"
        @sort-change="sortPinsChange"
        >

        <el-table-column
          label="ID"
          prop="id"
          sortable="custom"
          align="center"
          width="80"
          :class-name="getPinsSortClass('id')"
          >
          <template slot-scope="{row}">
            <span>{{ row.id }}</span>
          </template>
        </el-table-column>           
        <el-table-column label="PIN" width="140px" >
          <template slot-scope="{row}">
            <span>{{ row.pin_number }}</span>
          </template>
        </el-table-column>
        <el-table-column label="Package" width="120px" >
          <template slot-scope="{row}">
            <span>{{ row.package.name }}</span>
          </template>
        </el-table-column>
        <el-table-column label="Base Amount" width="120px" align="right">
          <template slot-scope="{row}">
            <span>{{ row.base_amount }}</span>
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
        <el-table-column label="Owner" width="130px" >
          <template slot-scope="{row}">
            <span>{{ row.owner?row.owner.user.username:'' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="Userd by" width="130px" >
          <template slot-scope="{row}">
            <span>{{ row.user?row.user.user.username:'' }}</span>
          </template>
        </el-table-column>
        <el-table-column label="Used at" width="150px" align="center">
          <template slot-scope="{row}">
            <span v-if="row.used_at">{{ row.used_at | parseTime('{y}-{m}-{d}') }}</span>
          </template>
        </el-table-column>
         <el-table-column label="Allocation date" width="150px" align="center">
          <template slot-scope="{row}">
            <span>{{ row.allocated_at | parseTime('{y}-{m}-{d}') }}</span>
          </template>
        </el-table-column>
        <el-table-column label="Note" min-width="150px" >
          <template slot-scope="{row}">
            <span>{{ row.note }}</span>
          </template>
        </el-table-column>
      
      </el-table>

      <pagination
        v-show="pinsTotal>0"
        :total="pinsTotal"
        :page.sync="pinsListQuery.page"
        :limit.sync="pinsListQuery.limit"
        @pagination="getPinsList"
      />
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogViewPinsVisible = false">
          Cancel
        </el-button>
      </div>
    </el-dialog>
  </div>
</template>

<script>
import { fetchMyApprovedPinRequests, fetchRequestPins } from "@/api/user/pins";

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
      pinsTableKey:1,
      list: [],
      total: 0,
      listLoading: false,
      listQuery: {
        page: 1,
        limit: 5,
        search:undefined,      
        sort: "+id"
      },
      paymentModes:[],
      packages:[],
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp:{
        id:undefined,
      },
      pinsList: [],
      pinsTotal: 0,      
      pinsListQuery: {
        page: 1,
        limit: 5,
        search:undefined,
        sort: "+id"
      },
      dialogTitle:'',
      dialogStatus: "",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      dialogViewPinsVisible:false,
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
      fetchMyApprovedPinRequests(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });
    },
    getPinsList() {
      this.listLoading = true;
      fetchRequestPins(this.pinsListQuery,this.temp.id).then(response => {
        this.pinsList = response.data.data;
        this.pinsTotal = response.data.total;
        this.listLoading = false;
      });
    },
    viewRequestPins(row) {
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogTitle = "Request Pins";
      this.temp = Object.assign({}, row);
      this.dialogViewPinsVisible = true;
      this.getPinsList();
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
    sortByID(order) {
      if (order === "ascending") {
        this.listQuery.sort = "+id";
      } else {
        this.listQuery.sort = "-id";
      }
      this.handleFilter();
    },
    sortPinsChange(data) {
      const { prop, order } = data;
      if (prop === "id") {
        this.sortPinsByID(order);
      }
    },
    handlePinsFilter() {
      this.pinsListQuery.page = 1;
      this.getPinsList();
    },
    sortPinsByID(order) {
      if (order === "ascending") {
        this.pinsListQuery.sort = "+id";
      } else {
        this.pinsListQuery.sort = "-id";
      }
      this.handlePinsFilter();
    },

    resetTemp() {
      this.temp = {
        id:undefined,
      };
    },
    getSortClass: function(key) {
      const sort = this.listQuery.sort;
      return sort === `+${key}`
        ? "ascending"
        : sort === `-${key}`
        ? "descending"
        : "";
    },
    getPinsSortClass: function(key) {
      const sort = this.pinsListQuery.sort;
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
