const express = require('express');
const db = require('../config/database');
const router = express.Router();

// Get all products (public)
router.get('/', async (req, res) => {
    try {
        const { category, gender, featured, limit = 50, offset = 0 } = req.query;
        
        let query = `
            SELECT 
                p.*,
                c.name as category_name,
                c.slug as category_slug,
                COALESCE(
                    json_agg(
                        DISTINCT jsonb_build_object(
                            'id', pi.id,
                            'image_url', pi.image_url,
                            'alt_text', pi.alt_text,
                            'is_primary', pi.is_primary
                        )
                    ) FILTER (WHERE pi.id IS NOT NULL), '[]'
                ) as images,
                COALESCE(
                    json_agg(
                        DISTINCT jsonb_build_object(
                            'id', pc.id,
                            'color_name', pc.color_name,
                            'color_code', pc.color_code,
                            'stock_quantity', pc.stock_quantity
                        )
                    ) FILTER (WHERE pc.id IS NOT NULL), '[]'
                ) as colors,
                COALESCE(
                    json_agg(
                        DISTINCT jsonb_build_object(
                            'id', ps.id,
                            'size_name', ps.size_name,
                            'stock_quantity', ps.stock_quantity
                        )
                    ) FILTER (WHERE ps.id IS NOT NULL), '[]'
                ) as sizes
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_images pi ON p.id = pi.product_id
            LEFT JOIN product_colors pc ON p.id = pc.product_id AND pc.is_active = true
            LEFT JOIN product_sizes ps ON p.id = ps.product_id AND ps.is_active = true
            WHERE p.is_active = true
        `;
        
        const params = [];
        let paramCount = 1;
        
        if (category) {
            query += ` AND c.slug = $${paramCount}`;
            params.push(category);
            paramCount++;
        }
        
        if (gender) {
            query += ` AND p.gender = $${paramCount}`;
            params.push(gender);
            paramCount++;
        }
        
        if (featured === 'true') {
            query += ` AND p.featured = true`;
        }
        
        query += `
            GROUP BY p.id, c.name, c.slug
            ORDER BY p.created_at DESC
            LIMIT $${paramCount} OFFSET $${paramCount + 1}
        `;
        
        params.push(parseInt(limit), parseInt(offset));
        
        const result = await db.query(query, params);
        
        res.json({
            success: true,
            products: result.rows,
            total: result.rows.length
        });
        
    } catch (error) {
        console.error('Get products error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Get single product by slug (public)
router.get('/:slug', async (req, res) => {
    try {
        const { slug } = req.params;
        
        const query = `
            SELECT 
                p.*,
                c.name as category_name,
                c.slug as category_slug,
                COALESCE(
                    json_agg(
                        DISTINCT jsonb_build_object(
                            'id', pi.id,
                            'image_url', pi.image_url,
                            'alt_text', pi.alt_text,
                            'is_primary', pi.is_primary,
                            'sort_order', pi.sort_order
                        )
                        ORDER BY pi.sort_order, pi.is_primary DESC
                    ) FILTER (WHERE pi.id IS NOT NULL), '[]'
                ) as images,
                COALESCE(
                    json_agg(
                        DISTINCT jsonb_build_object(
                            'id', pc.id,
                            'color_name', pc.color_name,
                            'color_code', pc.color_code,
                            'stock_quantity', pc.stock_quantity
                        )
                    ) FILTER (WHERE pc.id IS NOT NULL), '[]'
                ) as colors,
                COALESCE(
                    json_agg(
                        DISTINCT jsonb_build_object(
                            'id', ps.id,
                            'size_name', ps.size_name,
                            'stock_quantity', ps.stock_quantity
                        )
                        ORDER BY ps.size_order
                    ) FILTER (WHERE ps.id IS NOT NULL), '[]'
                ) as sizes
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_images pi ON p.id = pi.product_id
            LEFT JOIN product_colors pc ON p.id = pc.product_id AND pc.is_active = true
            LEFT JOIN product_sizes ps ON p.id = ps.product_id AND ps.is_active = true
            WHERE p.slug = $1 AND p.is_active = true
            GROUP BY p.id, c.name, c.slug
        `;
        
        const result = await db.query(query, [slug]);
        
        if (result.rows.length === 0) {
            return res.status(404).json({ error: 'Product not found' });
        }
        
        res.json({
            success: true,
            product: result.rows[0]
        });
        
    } catch (error) {
        console.error('Get product error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Get categories (public)
router.get('/categories/all', async (req, res) => {
    try {
        const result = await db.query(
            'SELECT * FROM categories WHERE is_active = true ORDER BY name'
        );
        
        res.json({
            success: true,
            categories: result.rows
        });
        
    } catch (error) {
        console.error('Get categories error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

// Search products (public)
router.get('/search/:term', async (req, res) => {
    try {
        const { term } = req.params;
        const { limit = 20 } = req.query;
        
        const query = `
            SELECT 
                p.*,
                c.name as category_name,
                COALESCE(
                    json_agg(
                        DISTINCT jsonb_build_object(
                            'id', pi.id,
                            'image_url', pi.image_url,
                            'is_primary', pi.is_primary
                        )
                    ) FILTER (WHERE pi.id IS NOT NULL AND pi.is_primary = true), '[]'
                ) as images
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN product_images pi ON p.id = pi.product_id
            WHERE p.is_active = true 
            AND (
                p.name ILIKE $1 
                OR p.description ILIKE $1 
                OR c.name ILIKE $1
            )
            GROUP BY p.id, c.name
            ORDER BY p.name
            LIMIT $2
        `;
        
        const searchTerm = `%${term}%`;
        const result = await db.query(query, [searchTerm, parseInt(limit)]);
        
        res.json({
            success: true,
            products: result.rows,
            searchTerm: term
        });
        
    } catch (error) {
        console.error('Search products error:', error);
        res.status(500).json({ error: 'Internal server error' });
    }
});

module.exports = router;
