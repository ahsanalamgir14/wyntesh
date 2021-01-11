<template>
  <div class="app-container">
    <div class="filter-container">
      <el-input v-model="listQuery.search" placeholder="Search Records" style="width: 200px;" class="filter-item mobile_class" size="mini" @keyup.enter.native="handleFilter" />
      <el-button v-waves size="mini" class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">Search</el-button>
      <el-button size="mini" class="filter-item" style="margin-left: 10px;" type="primary" icon="el-icon-plus" @click="handleCreate">Add</el-button>
    </div>
    <el-table :key="tableKey" v-loading="listLoading" :data="list" border fit highlight-current-row style="width: 100%;" @sort-change="sortChange">
      <el-table-column label="ID" prop="id" sortable="custom" align="center" width="80" :class-name="getSortClass('id')">
        <template slot-scope="{row}">
          <span>{{ row.id }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Actions" align="center" min-width="150" class-name="small-padding">
        <template slot-scope="{row}">
          <el-tooltip content="Edit" placement="right" effect="dark">
            <el-button type="primary" circle icon="el-icon-edit" :loading="buttonLoading" @click="handleEdit(row)"></el-button>
          </el-tooltip>
          <el-tooltip content="Delete" placement="right" effect="dark">
            <el-button type="danger" icon="el-icon-delete" circle :loading="buttonLoading" @click="handleDelete(row)"></el-button>
          </el-tooltip>
        </template>
      </el-table-column>
      <el-table-column label="Name" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.name }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Brand Size" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.brand_size }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Length mm" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.length_mm }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Width mm" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.width_mm }}</span>
        </template>
      </el-table-column>
      <el-table-column label="Height mm" width="150px" align="center">
        <template slot-scope="{row}">
          <span>{{ row.height_mm }}</span>
        </template>
      </el-table-column>
    </el-table>
    <pagination v-show="total>0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getList" />
    <el-dialog :title="'Size '+textMap[dialogStatus]" :fullscreen="true" width="80%" top="2vh" :visible.sync="dialogSizeVisible">
      <el-form ref="sizeVariant" :rules="rules" :model="temp">
        <el-tabs type="border-card">
          <el-tab-pane label="Details">
            <el-row :gutter="20">
              <el-col :xs="24" :sm="24" :md="5" :lg="5" :xl="5">
                <el-form-item label="Name" prop="name">
                  <el-input v-model="temp.name" />
                </el-form-item>
                <el-form-item label="Brand Size" prop="brand_size">
                  <el-input v-model="temp.brand_size" />
                </el-form-item>
                <el-form-item label="Length MM" prop="length_mm">
                  <el-input type="number" min=0 v-model="temp.length_mm" />
                </el-form-item>
                <el-form-item label="Width MM" prop="width_mm">
                  <el-input type="number" min=0 v-model="temp.width_mm" />
                </el-form-item>
                <el-form-item label="Height MM" prop="height_mm">
                  <el-input type="number" min=0 v-model="temp.height_mm" />
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="24" :md="5" :lg="5" :xl="5">
                <el-form-item label="Chest CM" prop="chest_cm">
                  <el-input type="number" min=0 v-model="temp.chest_cm" />
                </el-form-item>
                <el-form-item label="Bust CM" prop="bust_cm">
                  <el-input type="number" min=0 v-model="temp.bust_cm" />
                </el-form-item>
                <el-form-item label="To Fit Bust CM" prop="to_fit_bust_cm">
                  <el-input type="number" min=0 v-model="temp.to_fit_bust_cm" />
                </el-form-item>
                <el-form-item label="Front Length CM" prop="front_length_cm">
                  <el-input type="number" min=0 v-model="temp.front_length_cm" />
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="24" :md="5" :lg="5" :xl="5">
                <el-form-item label="Waist CM" prop="waist_cm">
                  <el-input type="number" min=0 v-model="temp.waist_cm" />
                </el-form-item>
                <el-form-item label="To Fit Waist" prop="to_fit_waist">
                  <el-input type="number" min=0 v-model="temp.to_fit_waist" />
                </el-form-item>
                <el-form-item label="Across Shoulder Cm" prop="across_shoulder_cm">
                  <el-input type="number" min=0 v-model="temp.across_shoulder_cm" />
                </el-form-item>
                <el-form-item label="Hips CM" prop="hips_cm">
                  <el-input type="number" min=0 v-model="temp.hips_cm" />
                </el-form-item>
              </el-col>
              <el-col :xs="24" :sm="24" :md="5" :lg="5" :xl="5">
                <el-form-item label="Inseam Length CM" prop="inseam_length_cm">
                  <el-input type="number" min=0 v-model="temp.inseam_length_cm" />
                </el-form-item>
                <el-form-item label="Top Length CM" prop="top_length_cm">
                  <el-input type="number" min=0 v-model="temp.top_length_cm" />
                </el-form-item>
                <el-form-item label="Bottom Length CM" prop="bottom_length_cm">
                  <el-input type="number" min=0 v-model="temp.bottom_length_cm" />
                </el-form-item>
                <el-form-item label="Sleeve Length CM" prop="sleeve_length_cm">
                  <el-input type="number" min=0 v-model="temp.sleeve_length_cm" />
                </el-form-item>
                <el-form-item label="Neck CM" prop="neck_cm">
                  <el-input type="number" min=0 v-model="temp.neck_cm" />
                </el-form-item>
              </el-col>
            </el-row>
          </el-tab-pane>
        </el-tabs>
      </el-form>
      <div slot="footer" class="dialog-footer">
        <el-button @click="dialogSizeVisible = false">
          Cancel
        </el-button>
        <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="dialogStatus==='create'?createData():updateData()">
          Save
        </el-button>
      </div>
    </el-dialog>
  </div>
</template>
<script>
import {
  fetchList,
  createSize,
  updateSize,
  deleteSize,
  changeSizeStatus
} from "@/api/admin/size-variants";
import { parse_currency } from "@/utils/currencies";
import waves from "@/directive/waves"; // waves directive
import { parseTime } from "@/utils";
import Pagination from "@/components/Pagination"; // secondary package based on el-pagination
import axios from "axios";
import role from '@/directive/role'; // Permission directive (v-role)

export default {
  name: "Packages",
  components: { Pagination },
  directives: { waves, role },
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
      list: [],
      total: 3,
      listLoading: false,
      listQuery: {
        page: 1,
        limit: 5,
        search: undefined,
        sort: "+id"
      },
      sortOptions: [
        { label: "ID Ascending", key: "+id" },
        { label: "ID Descending", key: "-id" }
      ],
      temp: {
        name: undefined,
        brand_size: undefined,
        length_mm: 0,
        width_mm: 0,
        height_mm: 0,
        chest_cm: 0,
        bust_cm: 0,
        to_fit_bust_cm: 0,
        front_length_cm: 0,
        waist_cm: 0,
        to_fit_waist: 0,
        across_shoulder_cm: 0,
        hips_cm: 0,
        inseam_length_cm: 0,
        top_length_cm: 0,
        bottom_length_cm: 0,
        sleeve_length_cm: 0,
        neck_cm: 0,
        is_active: 1,
      },
      rules: {
        name: [
          { required: true, message: "Name is required", trigger: "blur" }
        ],
        brand_size: [
          { required: true, message: "brand_size code is required", trigger: "blur" }
        ],
      },
      dialogSizeVisible: false,
      dialogStatus: "",
      textMap: {
        update: "Edit",
        create: "Create"
      },
      fileList: [],
      file: undefined,
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
      fetchList(this.listQuery).then(response => {
        this.list = response.data.data;
        this.total = response.data.total;
        setTimeout(() => {
          this.listLoading = false;
        }, 1 * 100);
      });

    },
    resetTemp() {
      this.temp = {
        name: undefined,
        brand_size: undefined,
        length_mm: 0,
        width_mm: 0,
        height_mm: 0,
        chest_cm: 0,
        bust_cm: 0,
        to_fit_bust_cm: 0,
        front_length_cm: 0,
        waist_cm: 0,
        to_fit_waist: 0,
        across_shoulder_cm: 0,
        hips_cm: 0,
        inseam_length_cm: 0,
        top_length_cm: 0,
        bottom_length_cm: 0,
        sleeve_length_cm: 0,
        neck_cm: 0,
        is_active: 1,
      };
      this.file = undefined;
      this.fileList = [];
    },
    handleModifyStatus(row, status) {
      let data = { 'id': row.id, 'is_active': status };
      changeSizeStatus(data).then((response) => {
        this.$notify({
          title: "Success",
          message: response.message,
          type: "success",
          duration: 2000
        })
      })
      row.is_active = status;
    },
    handleCreate() {
      this.resetTemp();
      this.dialogStatus = "create";
      this.dialogSizeVisible = true;
      this.$nextTick(() => {
        this.$refs["sizeVariant"].clearValidate();
      });
    },
    createData() {
      this.$refs["sizeVariant"].validate(valid => {
        if (valid) {
          this.buttonLoading = true;
          var form = new FormData();
          const form_data = this.temp;

          for (var key in form_data) {
            if (form_data[key] !== undefined && form_data[key] !== null) {
              form.append(key, form_data[key]);
            }
          }
          form.append('image', this.file);
          createSize(form).then((response) => {
            this.getList();
            this.dialogSizeVisible = false;
            this.buttonLoading = false;
            this.$notify({
              title: "Success",
              message: "Created Successfully",
              type: "success",
              duration: 2000
            })
          }).catch((res) => {
            this.buttonLoading = false;
          });
        }
      });
    },
    handleEdit(row) {
      this.file = undefined;
      this.fileList = [];
      this.temp = Object.assign({}, row);
      this.dialogStatus = "update";
      this.dialogSizeVisible = true;

      this.$nextTick(() => {
        this.$refs["sizeVariant"].clearValidate();
      });
    },
    updateData() {
      this.$refs["sizeVariant"].validate(valid => {
        if (valid) {

          var form = new FormData();
          const form_data = this.temp;

          for (var key in form_data) {
            if (form_data[key] !== undefined && form_data[key] !== null) {
              form.append(key, form_data[key]);
            }
          }

          form.append('image', this.file);
          this.buttonLoading = true;
          updateSize(form, form_data.id).then((response) => {
            this.getList();
            this.buttonLoading = false;
            this.dialogSizeVisible = false;
            this.$notify({
              title: "Success",
              message: "Update Successfully",
              type: "success",
              duration: 2000
            });
          }).catch((res) => {
            this.buttonLoading = false;
          });
        }
      });
    },
    handleDelete(row) {
      deleteSize(row.id).then((data) => {
        this.$notify({
          title: "Success",
          message: "Delete Successfully",
          type: "success",
          duration: 2000
        });
        this.getList();
      });
    },
    handleChange(f, fl) {
      if (fl.length > 1) {
        fl.shift();
      }
      this.file = f.raw;
    },
    handleRemove(file, fileList) {
      this.file = undefined;
      this.fileList = [];
    },
    handleExceed(files, fileList) {
      this.$message.warning(`You can not select more than one file, please remove first.`);
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
      return sort === `+${key}` ?
        "ascending" :
        sort === `-${key}` ?
        "descending" :
        "";
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

.edit-input {
  padding-right: 100px;
}

.cancel-btn {
  position: absolute;
  right: 15px;
  top: 10px;
}

</style>
