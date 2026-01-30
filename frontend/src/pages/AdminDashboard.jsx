import { useEffect, useState, useCallback } from 'react';
import api from '../services/api';
import { FaEdit, FaTrash, FaPlus } from 'react-icons/fa';

const AdminDashboard = () => {
    const [products, setProducts] = useState([]);
    const [loading, setLoading] = useState(true);
    const [activeTab, setActiveTab] = useState('products');

    // Simple mock for "Add Product" toggle for now
    const [showForm, setShowForm] = useState(false);
    const [formData, setFormData] = useState({
        name: '', description: '', price: '', stock: '', category_id: 1 // Default cat ID
    });
    const [image, setImage] = useState(null);

    const fetchProducts = useCallback((showLoading = true) => {
        if (showLoading) setLoading(true);
        api.get('/products')
            .then(res => setProducts(res.data.data.data))
            .catch(console.error)
            .finally(() => setLoading(false));
    }, []);

    useEffect(() => {
        fetchProducts(false);
    }, [fetchProducts]);

    const handleDelete = async (id) => {
        if (!confirm('Delete product?')) return;
        try {
            await api.delete(`/admin/products/${id}`);
            fetchProducts();
        } catch (err) {
            console.error(err);
            alert('Failed to delete');
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        const data = new FormData();
        Object.keys(formData).forEach(key => data.append(key, formData[key]));
        if (image) data.append('images[]', image);

        try {
            await api.post('/admin/products', data, {
                headers: { 'Content-Type': 'multipart/form-data' }
            });
            setShowForm(false);
            fetchProducts();
            alert('Product created');
        } catch (err) {
            console.error(err);
            alert('Failed to create product');
        }
    };

    return (
        <div className="max-w-6xl mx-auto bg-white p-6 shadow-md rounded">
            <h1 className="text-2xl font-bold mb-6">Admin Dashboard</h1>

            <div className="flex border-b mb-6">
                <button
                    className={`pb-2 px-4 ${activeTab === 'products' ? 'border-b-2 border-blue-600 font-bold' : ''}`}
                    onClick={() => setActiveTab('products')}
                >
                    Products
                </button>
                <button
                    className={`pb-2 px-4 ${activeTab === 'orders' ? 'border-b-2 border-blue-600 font-bold' : ''}`}
                    onClick={() => setActiveTab('orders')}
                >
                    Orders
                </button>
            </div>

            {activeTab === 'products' && (
                <div>
                    <div className="flex justify-between mb-4">
                        <h2 className="text-xl font-semibold">Manage Products</h2>
                        <button
                            onClick={() => setShowForm(!showForm)}
                            className="bg-blue-600 text-white px-4 py-2 rounded flex items-center"
                        >
                            <FaPlus className="mr-2" /> Add Product
                        </button>
                    </div>

                    {showForm && (
                        <form onSubmit={handleSubmit} className="mb-8 bg-gray-50 p-4 rounded border">
                            <div className="grid grid-cols-2 gap-4">
                                <input placeholder="Name" className="border p-2 rounded" required
                                    value={formData.name} onChange={e => setFormData({ ...formData, name: e.target.value })} />
                                <input placeholder="Price" type="number" className="border p-2 rounded" required
                                    value={formData.price} onChange={e => setFormData({ ...formData, price: e.target.value })} />
                                <input placeholder="Stock" type="number" className="border p-2 rounded" required
                                    value={formData.stock} onChange={e => setFormData({ ...formData, stock: e.target.value })} />
                                <input type="file" className="border p-2 rounded" onChange={e => setImage(e.target.files[0])} />
                                <textarea placeholder="Description" className="border p-2 rounded col-span-2" required
                                    value={formData.description} onChange={e => setFormData({ ...formData, description: e.target.value })} />
                            </div>
                            <button className="mt-4 bg-green-600 text-white px-4 py-2 rounded">Save Product</button>
                        </form>
                    )}

                    {loading ? <p>Loading...</p> : (
                        <table className="w-full text-left">
                            <thead>
                                <tr className="border-b">
                                    <th className="py-2">Image</th>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {products.map(product => (
                                    <tr key={product.id} className="border-b">
                                        <td className="py-2">
                                            {product.images && product.images.length > 0 && (
                                                <img src={`http://localhost:8000${product.images[0].image_path}`} className="w-10 h-10 object-cover" />
                                            )}
                                        </td>
                                        <td>{product.name}</td>
                                        <td>₹{product.price}</td>
                                        <td>{product.stock}</td>
                                        <td>
                                            <button onClick={() => handleDelete(product.id)} className="text-red-500"><FaTrash /></button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    )}
                </div>
            )}

            {activeTab === 'orders' && (
                <div>
                    <h2 className="text-xl font-semibold">Recent Orders</h2>
                    <p className="text-gray-500">Order management view would go here (similar table to user dashboard but with status edit).</p>
                </div>
            )}
        </div>
    );
};

export default AdminDashboard;
