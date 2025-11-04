# Product Rating System Documentation

## Overview
A comprehensive product rating and review system for the e-commerce platform with the following features:

## Features

### Customer Features
- **Star Rating System**: 1-5 star ratings for products
- **Detailed Reviews**: Title, description, pros/cons sections
- **Verified Purchase Badge**: Shows if reviewer actually bought the product
- **Helpful Voting**: Mark reviews as helpful
- **Review Sorting**: Sort by newest, oldest, highest/lowest rating, most helpful
- **Guest Reviews**: Non-registered users can leave reviews with name/email
- **Real-time Form**: Ajax-powered review submission

### Display Features
- **Rating Overview**: Visual breakdown with percentages for each star level
- **Average Rating Display**: Shows on product pages and listings
- **Review Count**: Total number of reviews per product
- **Time Stamps**: Shows when reviews were posted
- **Responsive Design**: Works on all device sizes

### Admin Features
- **Review Management**: Approve, reject, or mark reviews as pending
- **Status Filtering**: Filter reviews by status (pending/approved/rejected)
- **Product Filtering**: Filter reviews by specific products
- **Bulk Statistics**: Overall rating stats and metrics
- **Review Moderation**: Tools to manage review quality

### Homepage Integration
- **Rating Summary Widget**: Shows overall customer satisfaction
- **Recent Reviews**: Latest approved reviews
- **Top Rated Products**: Highest rated products showcase

## Database Structure

### Tables Created
1. **product_ratings**
   - `id`: Primary key
   - `product_id`: Foreign key to products table
   - `user_id`: Foreign key to users table (nullable for guest reviews)
   - `user_name`: Name of reviewer
   - `user_email`: Email of reviewer
   - `rating`: 1-5 star rating
   - `review_title`: Title of the review
   - `review_text`: Main review content
   - `pros`: Positive aspects (optional)
   - `cons`: Negative aspects (optional)
   - `verified_purchase`: Boolean for purchase verification
   - `helpful_count`: Number of helpful votes
   - `status`: pending/approved/rejected
   - `created_at`: Timestamp
   - `updated_at`: Timestamp

2. **rating_helpful**
   - `id`: Primary key
   - `rating_id`: Foreign key to product_ratings
   - `user_id`: Foreign key to users (nullable)
   - `user_ip`: IP address for guest voting
   - `created_at`: Timestamp
   - Unique constraint to prevent duplicate votes

## Files Structure

### Core Components
- `includes/rating_functions.php`: Core rating display and calculation functions
- `includes/rating_form.php`: Review submission form component
- `includes/rating_summary_widget.php`: Homepage rating summary widget

### API Endpoints
- `api/submit_rating.php`: Handle review submissions
- `api/mark_helpful.php`: Handle helpful vote submissions
- `api/get_reviews.php`: Fetch reviews with sorting options

### Admin Panel
- `admin/ratings.php`: Complete admin interface for managing reviews
- Added navigation links in `admin/feedback.php`

### Integration
- `product.php`: Updated with full rating display and review system
- `index.php`: Added rating summary widget to homepage

## Sample Data
The system includes sample reviews for all products:
- Smartphone Pro: 2 reviews (5-star and 4-star)
- Laptop Ultra: 1 review (5-star)
- Programming Book: 1 review (5-star)
- T-Shirt Basic: 1 review (4-star)

## Security Features
- **SQL Injection Protection**: All database queries use prepared statements
- **XSS Prevention**: All user input is properly escaped
- **Duplicate Prevention**: Users can only review each product once
- **Rate Limiting**: IP-based voting restrictions
- **Admin Moderation**: All reviews require approval before display

## Styling
- **Dark Theme**: Consistent with site's modern dark aesthetic
- **Glass Effects**: Backdrop blur and transparency effects
- **Orange Accents**: Matches the site's color scheme (#ff6b35)
- **Responsive**: Mobile-first design approach
- **Animations**: Smooth transitions and hover effects

## Usage Examples

### Display Product Rating
```php
require_once 'includes/rating_functions.php';
$stats = getProductRatingStats($pdo, $product_id);
echo displayStars($stats['average_rating']);
```

### Show Review Form
```php
require_once 'includes/rating_form.php';
echo displayRatingForm($product_id, $is_logged_in);
```

### Admin Management
Navigate to `/admin/ratings.php` to:
- View all reviews with filtering and sorting
- Approve or reject pending reviews
- Monitor rating statistics
- Manage review quality

## Future Enhancements
- Image uploads in reviews
- Review responses from store owners
- Advanced analytics and reporting
- Email notifications for new reviews
- Review incentive programs
- Review import/export functionality

## Technical Notes
- Compatible with PHP 5.5+
- Uses Bootstrap 5 for styling
- FontAwesome icons for star displays
- Ajax for smooth user interactions
- MySQL database with proper indexing
- SEO-friendly review markup potential