<template>
<div class="app-container">
    <div class="filter-container">
        <el-input v-model="listQuery.search" placeholder="Search Records" style="width: 200px" class="filter-item" @keyup.enter.native="handleFilter" />
        <el-button v-waves class="filter-item" type="primary" icon="el-icon-search" @click="handleFilter">Search</el-button>
        <el-button class="filter-item" style="margin-left: 10px" type="primary" icon="el-icon-plus" @click="handleCreate">Add</el-button>
    </div>

    <el-table :key="tableKey" v-loading="listLoading" :data="list" border fit highlight-current-row style="width: 100%" @sort-change="sortChange">
        <el-table-column label="ID" prop="id" sortable="custom" align="center" width="80" :class-name="getSortClass('id')">
            <template slot-scope="{ row }">
                <span>{{ row.id }}</span>
            </template>
        </el-table-column>
        <el-table-column label="Actions" align="center" width="150" class-name="small-padding">
            <template slot-scope="{ row }">
                <el-button type="primary" circle icon="el-icon-edit" :loading="buttonLoading" @click="handleEdit(row)"></el-button>
                <el-button v-role="['superadmin']" type="danger" icon="el-icon-delete" circle :loading="buttonLoading" @click="handleDelete(row)"></el-button>
                <el-button icon="el-icon-turn-off" circle v-if="row.is_active != 1" type="info" @click="handleModifyStatus(row, 1)">
                </el-button>
                <el-button icon="el-icon-open" circle v-if="row.is_active != 0" type="success" @click="handleModifyStatus(row, 0)">
                </el-button>
            </template>
        </el-table-column>
        <el-table-column label="Name" width="150px">
            <template slot-scope="{ row }">
                <span>{{ row.name }}</span>
            </template>
        </el-table-column>
        <el-table-column label="Combo Code" width="150px">
            <template slot-scope="{ row }">
                <span>{{ row.combo_code }}</span>
            </template>
        </el-table-column>
        <el-table-column label="Base Amount" width="150px">
            <template slot-scope="{ row }">
                <span>{{ row.base_amount }}</span>
            </template>
        </el-table-column>
        <el-table-column label="GST Rate" width="150px">
            <template slot-scope="{ row }">
                <span>{{ row.gst_rate }}</span>
            </template>
        </el-table-column>
        <el-table-column label="GST Amount" width="150px">
            <template slot-scope="{ row }">
                <span>{{ row.gst_amount }}</span>
            </template>
        </el-table-column>
        <el-table-column label="Net Amount" width="150px">
            <template slot-scope="{ row }">
                <span>{{ row.net_amount }}</span>
            </template>
        </el-table-column>
        <el-table-column label="Created At" width="150px" align="center">
            <template slot-scope="{ row }">
                <span>{{ row.created_at | parseTime('{y}-{m}-{d}') }}</span>
            </template>
        </el-table-column>
    </el-table>

    <pagination v-show="total > 0" :total="total" :page.sync="listQuery.page" :limit.sync="listQuery.limit" @pagination="getList" />
    <el-dialog :title="'Combo ' + textMap[dialogStatus]" :fullscreen="false" width="80%" top="2vh" :visible.sync="dialogComboVisible">
        <el-form ref="comboForm" :rules="rules" :model="temp">
            <el-tabs type="border-card" v-loading="listLoading">
                <el-tab-pane label="Details">
                    <el-row :gutter="20">
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                            <el-form-item label="Name" prop="name">
                                <el-input v-model="temp.name" />
                            </el-form-item>
                            <el-form-item label="Combo Code" prop="combo_code">
                                <el-input v-model="temp.combo_code" />
                            </el-form-item>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                            <el-form-item :label="pvLabel" prop="pv">
                                <el-input type="number" min="0" v-model="temp.pv" />
                            </el-form-item>
                            <el-form-item label="Description" prop="description">
                                <el-input type="textarea" v-model="temp.description" :rows="2" placeholder="Please Enter description">
                                </el-input>
                            </el-form-item>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                            <div class="img-upload">
                                <el-form-item prop="image">
                                    <label for="Combo Image">Combo Image</label>
                                    <el-upload ref="upload" class="avatar-uploader" action="#" :show-file-list="true" :auto-upload="false" :on-change="handleChange" :on-remove="handleRemove" :limit="1" :file-list="fileList" :on-exceed="handleExceed" accept="image/png, image/jpeg">
                                        <img v-if="temp.image" :src="temp ? temp.image : ''" class="avatar" />
                                        <i v-if="temp.image" slot="default" class="el-icon-plus" />
                                        <i v-else class="el-icon-plus avatar-uploader-icon" />
                                    </el-upload>
                                    <a v-if="temp.image" :href="temp ? temp.image : ''" target="_blank">View full image.</a>
                                </el-form-item>
                            </div>
                        </el-col>
                    </el-row>
                </el-tab-pane>
                <el-tab-pane label="Categories Details">
                    <el-row :gutter="20">
                        <el-col :xs="24" :sm="24" :md="12" :lg="12" :xl="12">
                            <el-button type="success" size="mini" @click="handleAddCategory" style="margin-bottom: 10px">
                                Add category to Combo
                            </el-button>
                            <router-link to="/products/add"> </router-link>
                        </el-col>
                    </el-row>
                    <el-row :gutter="20">
                        <el-col :xs="24" :sm="24" :md="16" :lg="16" :xl="16">
                            <el-table :data="temp.categories" border style="width: 100%">
                                <el-table-column label="Name" min-width="150px">
                                    <template slot-scope="{ row }">
                                        <span>{{ row.category.name }}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column label="Quantity" width="150px">
                                    <template slot-scope="{ row }">
                                        <span>{{ row.quantity }}</span>
                                    </template>
                                </el-table-column>
                                <el-table-column label="Actions" align="center" width="150" class-name="small-padding">
                                    <template slot-scope="{ row }">
                                        <el-tooltip content="Edit" placement="right" effect="dark">
                                            <el-button type="primary" circle icon="el-icon-edit" :loading="buttonLoading" @click="handleCategoryEdit(row)"></el-button>
                                        </el-tooltip>
                                        <el-tooltip content="Delete" placement="right" effect="dark">
                                            <el-button type="danger" icon="el-icon-delete" circle :loading="buttonLoading" @click="handleCategoryDelete(row)"></el-button>
                                        </el-tooltip>
                                    </template>
                                </el-table-column>
                            </el-table>
                        </el-col>
                        <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                            <el-form-item label="Base Amount" prop="base_amount">
                                <el-input type="number" min="0" v-model="temp.base_amount" />
                            </el-form-item>
                            <el-form-item label="Final Price" prop="net_amount">
                                <el-input type="number" min="1" v-model="temp.net_amount" />
                            </el-form-item>
                            <el-form-item label="MRP" prop="mrp">
                                <el-input type="number" min="1" v-model="temp.mrp" />
                            </el-form-item>
                        </el-col>
                    </el-row>
                </el-tab-pane>
            </el-tabs>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button @click="dialogComboVisible = false"> Cancel </el-button>
            <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="dialogStatus === 'create' ? createData() : updateData()">
                Save
            </el-button>
        </div>
    </el-dialog>
    <el-dialog :title="dialogCategoryTitle" :fullscreen="false" width="80%" top="2vh" :visible.sync="dialogCategoryAddVisible">
        <el-form ref="categoryForm" :model="category" :rules="categoryRules">
            <el-row :gutter="20">
                <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                    <el-form-item label="categories" prop="category_id">
                        <br />
                        <el-select v-model="category.category_id" size="mini" @change="handleChangeCategory" style="width: 100%" filterable placeholder="Select Product">
                            <el-option v-for="item in categories" :key="item.name" :label="item.name" :value="item.id">
                            </el-option>
                        </el-select>
                    </el-form-item>
                    <el-form-item label="Quantity" prop="quantity">
                        <el-input type="number" min="1" v-model="category.quantity" />
                    </el-form-item>
                </el-col>
                <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                    <el-form-item label="DP Base" prop="dp_base">
                        <el-input type="number" size="mini" min="1" v-model="category.dp_base" />
                    </el-form-item>
                    <el-form-item label="DP GST Rate" prop="dp_gst_rate">
                        <el-input type="number" size="mini" min="1" v-model="category.dp_gst_rate" @change="calculateFinalDPPrice" />
                    </el-form-item>
                    <el-form-item label="DP GST Amount" prop="dp_gst_amount">
                        <el-input type="number" size="mini" min="1" v-model="category.dp_gst_amount" />
                    </el-form-item>
                    <el-form-item label="DP CGST Rate" prop="dp_cgst_rate">
                        <el-input type="number" size="mini" min="1" v-model="category.dp_cgst_rate" />
                    </el-form-item>
                    <el-form-item label="DP CGST Amount" prop="dp_cgst_amount">
                        <el-input type="number" size="mini" min="1" v-model="category.dp_cgst_amount" />
                    </el-form-item>
                </el-col>
                <el-col :xs="24" :sm="24" :md="8" :lg="8" :xl="8">
                    <el-form-item label="DP SGST Rate" prop="dp_sgst_rate">
                        <el-input type="number" size="mini" min="1" v-model="category.dp_sgst_rate" />
                    </el-form-item>
                    <el-form-item label="DP SGST Amount" prop="dp_sgst_amount">
                        <el-input type="number" size="mini" min="1" v-model="category.dp_sgst_amount" />
                    </el-form-item>
                    <el-form-item label="DP UTGST Rate" prop="dp_utgst_rate">
                        <el-input type="number" size="mini" min="1" v-model="category.dp_utgst_rate" />
                    </el-form-item>
                    <el-form-item label="DP UTGST Amount" prop="dp_utgst_amount">
                        <el-input type="number" size="mini" min="1" v-model="category.dp_utgst_amount" />
                    </el-form-item>
                    <el-form-item label="DP Amount" prop="dp_amount">
                        <el-input type="number" size="mini" min="1" v-model="category.dp_amount" />
                    </el-form-item>
                </el-col>
            </el-row>
        </el-form>
        <div slot="footer" class="dialog-footer">
            <el-button @click="dialogCategoryAddVisible = false">
                Cancel
            </el-button>
            <el-button type="primary" icon="el-icon-finished" :loading="buttonLoading" @click="
            dialogCategoryStatus === 'create'
              ? addCategory()
              : updateCategory()
          ">
                {{
            dialogCategoryStatus == 'create'
              ? 'Add Category'
              : 'Update Category'
          }}
            </el-button>
        </div>
    </el-dialog>
</div>
</template>

<script>
import {
    fetchList,
    createCombo,
    updateCombo,
    deleteCombo,
    changeComboStatus,
    categoryDelete,
} from '@/api/admin/combos';
import {
    getAllCategories
} from '@/api/admin/products-and-categories';

import waves from '@/directive/waves'; // waves directive
import {
    parseTime
} from '@/utils';
import Pagination from '@/components/Pagination'; // secondary Combo based on el-pagination
import axios from 'axios';
import role from '@/directive/role'; // Permission directive (v-role)
import defaultSettings from '@/settings';
const {
    pvLabel
} = defaultSettings;

export default {
    name: 'Combos',
    components: {
        Pagination,
    },
    directives: {
        waves,
        role,
    },
    filters: {
        statusFilter(status) {
            const statusMap = {
                1: 'success',
                draft: 'info',
                0: 'danger',
            };

            return statusMap[status];
        },
    },
    data() {
        return {
            pvLabel,
            tableKey: 0,
            list: [],
            total: 3,
            listLoading: false,
            listQuery: {
                page: 1,
                limit: 5,
                search: undefined,
                sort: '+id',
            },
            sortOptions: [{
                    label: 'ID Ascending',
                    key: '+id',
                },
                {
                    label: 'ID Descending',
                    key: '-id',
                },
            ],
            categories: [],
            temp: {
                name: undefined,
                combo_code: undefined,
                description: undefined,
                image: undefined,
                base_amount: undefined,
                gst_rate: undefined,
                gst_amount: undefined,
                cgst_rate: undefined,
                cgst_amount: undefined,
                sgst_rate: undefined,
                sgst_amount: undefined,
                utgst_rate: undefined,
                utgst_amount: undefined,
                net_amount: undefined,
                mrp: undefined,
                pv: 0,
                is_active: 1,
                categories: [],
            },
            rules: {
                name: [{
                    required: true,
                    message: 'Name is required',
                    trigger: 'blur',
                }, ],
                combo_code: [{
                    required: true,
                    message: 'Combo code is required',
                    trigger: 'blur',
                }, ],
                base_amount: [{
                    required: true,
                    message: 'Enter base amount.',
                    trigger: 'blur',
                }, ],
                gst_rate: [{
                    required: true,
                    message: 'Enter GST Percent.',
                    trigger: 'blur',
                }, ],
                gst_amount: [{
                    required: true,
                    message: 'GST amount is required.',
                    trigger: 'blur',
                }, ],
                sgst_rate: [{
                    required: true,
                    message: 'Enter SGST Percent.',
                    trigger: 'blur',
                }, ],
                sgst_amount: [{
                    required: true,
                    message: 'SGST amount is required.',
                    trigger: 'blur',
                }, ],
                cgst_rate: [{
                    required: true,
                    message: 'Enter CGST Percent.',
                    trigger: 'blur',
                }, ],
                cgst_amount: [{
                    required: true,
                    message: 'CGST amount is required.',
                    trigger: 'blur',
                }, ],
                net_amount: [{
                    required: true,
                    message: 'Net amount is required.',
                    trigger: 'blur',
                }, ],
                mrp: [{
                    required: true,
                    message: 'mrp amount is required.',
                    trigger: 'blur',
                }, ],
                pv: [{
                    required: true,
                    message: 'PV is required.',
                    trigger: 'blur',
                }, ],
            },
            category: {
                id: undefined,
                category_id: undefined,
                category: {
                    name: undefined,
                },
                quantity: 1,
                dp_base: 0,
                dp_gst_rate: 0,
                dp_gst_amount: 0,
                dp_cgst_rate: 0,
                dp_cgst_amount: 0,
                dp_sgst_rate: 0,
                dp_sgst_amount: 0,
                dp_utgst_rate: 0,
                dp_utgst_amount: 0,
                dp_amount: 0,
                pv: undefined,
            },
            categoryRules: {
                category_id: [{
                    required: true,
                    message: 'Please select categories.',
                    trigger: 'blur',
                }, ],
                quantity: [{
                    required: true,
                    message: 'Quantity is required',
                    trigger: 'blur',
                }, ],
                dp_base: [{
                    required: true,
                    message: 'DP Base is required',
                    trigger: 'blur',
                }, ],
                dp_gst_rate: [{
                    required: true,
                    message: 'DP GST rate is required',
                    trigger: 'blur',
                }, ],
                dp_gst_amount: [{
                    required: true,
                    message: 'DP GST amount is required',
                    trigger: 'blur',
                }, ],
                dp_cgst_rate: [{
                    required: true,
                    message: 'DP CGST rate is required',
                    trigger: 'blur',
                }, ],
                dp_cgst_amount: [{
                    required: true,
                    message: 'DP CGST amount is required',
                    trigger: 'blur',
                }, ],
                dp_sgst_rate: [{
                    required: true,
                    message: 'DP SGST rate is required',
                    trigger: 'blur',
                }, ],
                dp_sgst_amount: [{
                    required: true,
                    message: 'DP SGST amount is required',
                    trigger: 'blur',
                }, ],
                dp_utgst_rate: [{
                    required: true,
                    message: 'DP UTGST rate is required',
                    trigger: 'blur',
                }, ],
                dp_utgst_amount: [{
                    required: true,
                    message: 'DP UTGST amount is required',
                    trigger: 'blur',
                }, ],
                dp_amount: [{
                    required: true,
                    message: 'DP amount is required',
                    trigger: 'blur',
                }, ],
            },
            dialogComboVisible: false,
            dialogStatus: '',
            dialogCategoryAddVisible: false,
            dialogCategoryTitle: 'Add Categorie',
            dialogCategoryStatus: 'create',
            textMap: {
                update: 'Edit',
                create: 'Create',
            },
            oldCategory:undefined,
            fileList: [],
            file: undefined,
            downloadLoading: false,
            buttonLoading: false,
        };
    },
    created() {
        this.getList();
        getAllCategories().then((response) => {
            this.categories = response.data;
        });
    },
    methods: {
        getList() {
            this.listLoading = true;
            fetchList(this.listQuery).then((response) => {
                this.list = response.data.data;
                this.total = response.data.total;
                setTimeout(() => {
                    this.listLoading = false;
                }, 1 * 100);
            });
        },
        calculateFinalDPPrice() {
            if (
                this.category.dp_gst_rate != undefined &&
                this.category.dp_gst_rate != null
            ) {
                if (this.category.gst_rate == 0) {
                    this.category.dp_amount = 0;
                    this.category.dp_cgst_rate = 0;
                    this.category.dp_cgst_amount = 0;
                    this.category.dp_sgst_rate = 0;
                    this.category.dp_sgst_amount = 0;
                    this.category.dp_utgst_rate = 0;
                    this.category.dp_utgst_amount = 0;
                    this.category.dp_amount = this.category.dp_base;
                } else {
                    let gst = (this.category.dp_gst_rate * this.category.dp_base) / 100;
                    gst = Math.floor(gst);
                    this.category.dp_gst_amount = gst;
                    this.category.dp_cgst_rate = this.category.dp_gst_rate / 2;
                    this.category.dp_cgst_amount = gst / 2;
                    this.category.dp_sgst_rate = this.category.dp_gst_rate / 2;
                    this.category.dp_sgst_amount = gst / 2;
                    this.category.dp_utgst_rate = 0;
                    this.category.dp_utgst_amount = 0;
                    this.category.dp_amount = parseInt(this.category.dp_base) + gst;
                }
            }
        },
        handleChangeCategory() {
            if (this.category.category_id) {
                let category = this.categories.filter((category) => {
                    return this.category.category_id == category.id;
                })[0];

                this.category = {
                    category_id: category.id,
                    category: {
                        name: category.name,
                    },
                    quantity: 1,
                    dp_base: category.dp_base,
                    dp_gst_rate: category.dp_gst_rate,
                    dp_gst_amount: category.dp_gst_amount,
                    dp_cgst_rate: category.dp_cgst_rate,
                    dp_cgst_amount: category.dp_cgst_amount,
                    dp_sgst_rate: category.dp_sgst_rate,
                    dp_sgst_amount: category.dp_sgst_amount,
                    dp_utgst_rate: category.dp_utgst_rate,
                    dp_utgst_amount: category.dp_utgst_amount,
                    dp_amount: category.dp_amount,
                    pv: category.pv,
                };
            } else {
                this.category = [];
            }
        },
        handleAddCategory() {
            this.category = {
                id: undefined,
                category_id: undefined,
                category: {
                    name: undefined,
                },
                quantity: 1,
                dp_base: 0,
                dp_gst_rate: 0,
                dp_gst_amount: 0,
                dp_cgst_rate: 0,
                dp_cgst_amount: 0,
                dp_sgst_rate: 0,
                dp_sgst_amount: 0,
                dp_utgst_rate: 0,
                dp_utgst_amount: 0,
                dp_amount: 0,
                pv: undefined,
            };
            this.dialogCategoryAddVisible = true;
        },
        addCategory() {
            this.$refs['categoryForm'].validate((valid) => {
                if (valid) {
                    if (
                        this.temp.categories.some(
                            (el) => el.category_id == this.category.category_id
                        )
                    ) {
                        this.$message.error('categorie is already added.');
                        return false;
                    }

                    let category = {
                        category_id: this.category.category_id,
                        category: {
                            name: this.category.category.name,
                        },
                        quantity: this.category.quantity,
                        dp_base: this.category.dp_base,
                        dp_gst_rate: this.category.dp_gst_rate,
                        dp_gst_amount: this.category.dp_gst_amount,
                        dp_cgst_rate: this.category.dp_cgst_rate,
                        dp_cgst_amount: this.category.dp_cgst_amount,
                        dp_sgst_rate: this.category.dp_sgst_rate,
                        dp_sgst_amount: this.category.dp_sgst_amount,
                        dp_utgst_rate: this.category.dp_utgst_rate,
                        dp_utgst_amount: this.category.dp_utgst_amount,
                        dp_amount: this.category.dp_amount,
                        pv: this.category.pv,
                    };
                    this.temp.categories.push(category);
                    console.log(this.temp.categories);
                    this.calculatePrice();
                    this.dialogCategoryAddVisible = false;
                }
            });
        },
        handleCategoryEdit(row) {
            this.category = Object.assign({}, row);
            this.oldCategory = row.category_id;
            this.dialogCategoryAddVisible = true;
            this.dialogCategoryStatus = 'update';
            this.dialogCategoryTitle = 'Update Category';
        },
        updateCategory() {
            this.$refs['categoryForm'].validate((valid) => {
                if (valid) {
                    let tempCategories = this.temp.categories.filter((item) => {
                        return item.category_id !== this.oldCategory
                    });
                    
                    if (tempCategories.some(el => el.category_id == this.category.category_id)) {
                        this.$message.error('Categorie is already exists.');
                        return false;
                    }

                    let category = {
                        category_id: this.category.category_id,
                        category: {
                            name: this.category.category.name,
                        },
                        quantity: this.category.quantity,
                        dp_base: this.category.dp_base,
                        dp_gst_rate: this.category.dp_gst_rate,
                        dp_gst_amount: this.category.dp_gst_amount,
                        dp_cgst_rate: this.category.dp_cgst_rate,
                        dp_cgst_amount: this.category.dp_cgst_amount,
                        dp_sgst_rate: this.category.dp_sgst_rate,
                        dp_sgst_amount: this.category.dp_sgst_amount,
                        dp_utgst_rate: this.category.dp_utgst_rate,
                        dp_utgst_amount: this.category.dp_utgst_amount,
                        dp_amount: this.category.dp_amount,
                        pv: this.category.pv,
                    };
                    tempCategories.push(category);
                    this.temp.categories=tempCategories;
                    this.calculatePrice();
                    this.dialogCategoryAddVisible = false;
                }
            });
        },
         handleCategoryDelete(row) {
            this.temp.categories = this.temp.categories.filter((item) => {
                return item.category_id !== row.category_id
            });
            this.calculatePrice();
        },
        calculatePrice() {
            this.temp.base_amount = parseFloat(
                this.temp.categories
                .map((o) => o.dp_base * o.quantity)
                .reduce((a, c) => {
                    return parseFloat(a) + parseFloat(c);
                })
            );
            this.temp.net_amount = parseFloat(
                this.temp.categories
                .map((o) => o.dp_amount * o.quantity)
                .reduce((a, c) => {
                    return parseFloat(a) + parseFloat(c);
                })
            );
            this.temp.gst_amount = parseFloat(
                this.temp.categories
                .map((o) => o.dp_gst_amount * o.quantity)
                .reduce((a, c) => {
                    return parseFloat(a) + parseFloat(c);
                })
            );
            this.temp.sgst_amount = parseFloat(
                this.temp.categories
                .map((o) => o.dp_sgst_amount * o.quantity)
                .reduce((a, c) => {
                    return parseFloat(a) + parseFloat(c);
                })
            );
            this.temp.cgst_amount = parseFloat(
                this.temp.categories
                .map((o) => o.dp_cgst_amount * o.quantity)
                .reduce((a, c) => {
                    return parseFloat(a) + parseFloat(c);
                })
            );
            this.temp.utgst_amount = parseFloat(
                this.temp.categories
                .map((o) => o.dp_utgst_amount * o.quantity)
                .reduce((a, c) => {
                    return parseFloat(a) + parseFloat(c);
                })
            );

            this.temp.mrp = parseFloat(
                this.temp.categories
                .map((o) => o.dp_amount * o.quantity)
                .reduce((a, c) => {
                    return parseFloat(a) + parseFloat(c);
                })
            );
        },
        handleFilter() {
            this.listQuery.page = 1;
            this.getList();
        },
        sortChange(data) {
            const {
                prop,
                order
            } = data;
            if (prop === 'id') {
                this.sortByID(order);
            }
        },
        sortByID(order) {
            if (order === 'ascending') {
                this.listQuery.sort = '+id';
            } else {
                this.listQuery.sort = '-id';
            }
            this.handleFilter();
        },
        resetTemp() {
            this.temp = {
                name: undefined,
                combo_code: undefined,
                description: undefined,
                image: undefined,
                base_amount: undefined,
                cgst_rate: undefined,
                cgst_amount: undefined,
                sgst_rate: undefined,
                sgst_amount: undefined,
                utgst_rate: undefined,
                utgst_amount: undefined,
                net_amount: undefined,
                mrp: undefined,
                pv: 0,
                is_active: 1,
                categories: [],
            };
            this.file = undefined;
            this.fileList = [];
        },
        handleModifyStatus(row, status) {
            let data = {
                id: row.id,
                is_active: status,
            };
            changeComboStatus(data).then((response) => {
                this.$notify({
                    title: 'Success',
                    message: response.message,
                    type: 'success',
                    duration: 2000,
                });
            });

            row.is_active = status;
        },
        handleCreate() {
            this.resetTemp();
            this.dialogStatus = 'create';
            this.dialogComboVisible = true;
            this.$nextTick(() => {
                this.$refs['comboForm'].clearValidate();
            });
        },
        createData() {
            this.$refs['comboForm'].validate((valid) => {
                if (valid) {
                    this.buttonLoading = true;
                    var form = new FormData();
                    const form_data = this.temp;

                    for (var key in form_data) {
                        if (form_data[key] !== undefined && form_data[key] !== null) {
                            form.append(key, form_data[key]);
                        }
                    }

                    form.append('categories', JSON.stringify(this.temp.categories));

                    form.append('image', this.file);
                    createCombo(form)
                        .then((response) => {
                            this.getList();
                            this.dialogComboVisible = false;
                            this.buttonLoading = false;
                            this.$notify({
                                title: 'Success',
                                message: 'Created Successfully',
                                type: 'success',
                                duration: 2000,
                            });
                        })
                        .catch((res) => {
                            this.buttonLoading = false;
                        });
                }
            });
        },
        handleEdit(row) {
            this.file = undefined;
            this.fileList = [];
            this.temp = Object.assign({}, row);
            this.dialogStatus = 'update';
            this.dialogComboVisible = true;

            this.$nextTick(() => {
                this.$refs['comboForm'].clearValidate();
            });
        },
        updateData() {
            this.$refs['comboForm'].validate((valid) => {
                if (valid) {
                    var form = new FormData();
                    const form_data = this.temp;

                    for (var key in form_data) {
                        if (form_data[key] !== undefined && form_data[key] !== null) {
                            form.append(key, form_data[key]);
                        }
                    }
                    form.append('categories', JSON.stringify(this.temp.categories));

                    form.append('image', this.file);
                    this.buttonLoading = true;
                    updateCombo(form, form_data.id)
                        .then((response) => {
                            this.getList();
                            this.buttonLoading = false;
                            this.dialogComboVisible = false;
                            this.$notify({
                                title: 'Success',
                                message: 'Update Successfully',
                                type: 'success',
                                duration: 2000,
                            });
                        })
                        .catch((res) => {
                            this.buttonLoading = false;
                        });
                }
            });
        },
        handleDelete(row) {
            deleteCombo(row.id).then((data) => {
                this.$notify({
                    title: 'Success',
                    message: 'Delete Successfully',
                    type: 'success',
                    duration: 2000,
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
            this.$message.warning(
                `You can not select more than one file, please remove first.`
            );
        },

        getSortClass: function (key) {
            const sort = this.listQuery.sort;
            return sort === `+${key}` ?
                'ascending' :
                sort === `-${key}` ?
                'descending' :
                '';
        },
    },
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
