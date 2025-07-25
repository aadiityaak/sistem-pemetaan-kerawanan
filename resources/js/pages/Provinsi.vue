<template>
    <div>
        <h1>Daftar Provinsi</h1>
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="item in provinsi" :key="item.kode">
                    <td>{{ item.kode }}</td>
                    <td>{{ item.nama }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>

<script setup>
defineProps({ provinsi: Array });
</script>
