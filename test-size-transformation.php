<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Size Transformation Test</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .test-section {
            margin: 30px 0;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .size-selector-button {
            display: inline-flex;
            justify-content: center;
            align-items: center;
            width: 3rem;
            height: 3rem;
            border: 1px solid #ccc;
            border-radius: 50%;
            cursor: pointer;
            margin: 5px;
            transition: all 0.2s ease;
        }
        .size-selector-button.selected {
            background-color: #000;
            color: #fff;
            border-color: #000;
        }
        .size-selector-button.out-of-stock {
            background-color: #f5f5f5;
            color: #b0b0b0;
            text-decoration: line-through;
            cursor: not-allowed;
            pointer-events: none;
            border-color: #e0e0e0;
        }
        select {
            padding: 10px;
            margin: 10px 0;
            min-width: 200px;
        }
        .original {
            color: #666;
            text-decoration: line-through;
        }
        .transformed {
            color: #ee81b3;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>Clothing Size Transformation Test</h1>
    
    <div class="test-section">
        <h2>Size Selector Buttons</h2>
        <p>These should be transformed from full names to abbreviations:</p>
        
        <div class="size-selector-button">XSmall</div>
        <div class="size-selector-button">Small</div>
        <div class="size-selector-button">Medium</div>
        <div class="size-selector-button">Large</div>
        <div class="size-selector-button">XLarge</div>
        <div class="size-selector-button">XXLarge</div>
        <div class="size-selector-button">XXXLarge</div>
        <div class="size-selector-button">One Size</div>
        <div class="size-selector-button">Small/Medium</div>
        <div class="size-selector-button">Medium/Large</div>
        <div class="size-selector-button">Large/XLarge</div>
        <div class="size-selector-button">XSmall/Small</div>
    </div>
    
    <div class="test-section">
        <h2>Size Dropdown</h2>
        <p>This select dropdown should have transformed options:</p>
        
        <select name="attribute_select-size" class="variation-select size-attribute">
            <option value="">Choose a size</option>
            <option value="xsmall">XSmall</option>
            <option value="small">Small</option>
            <option value="medium">Medium</option>
            <option value="large">Large</option>
            <option value="xlarge">XLarge</option>
            <option value="xxlarge">XXLarge</option>
            <option value="one-size">One Size</option>
            <option value="small-medium">Small/Medium</option>
        </select>
    </div>
    
    <div class="test-section">
        <h2>Attribute Labels</h2>
        <p>These labels should be transformed:</p>
        
        <div class="attribute-label">XSmall</div>
        <div class="attribute-label">Small</div>
        <div class="attribute-label">Medium</div>
        <div class="attribute-label">Large</div>
        <div class="attribute-label">XLarge</div>
        <div class="attribute-label">One Size</div>
    </div>
    
    <div class="test-section">
        <h2>Data Attribute Elements</h2>
        <p>Elements with data-size-transform should be transformed:</p>
        
        <div data-size-transform="true">XSmall</div>
        <div data-size-transform="true">Small</div>
        <div data-size-transform="true">Medium</div>
        <div data-size-transform="true">Large</div>
        <div data-size-transform="true">XLarge</div>
    </div>
    
    <div class="test-section">
        <h2>Expected Transformations</h2>
        <table>
            <tr>
                <th>Original</th>
                <th>Transformed</th>
            </tr>
            <tr>
                <td class="original">XSmall/Small</td>
                <td class="transformed">XS/S</td>
            </tr>
            <tr>
                <td class="original">One Size</td>
                <td class="transformed">OS</td>
            </tr>
            <tr>
                <td class="original">XSmall</td>
                <td class="transformed">XS</td>
            </tr>
            <tr>
                <td class="original">Small</td>
                <td class="transformed">S</td>
            </tr>
            <tr>
                <td class="original">Medium</td>
                <td class="transformed">M</td>
            </tr>
            <tr>
                <td class="original">Large</td>
                <td class="transformed">L</td>
            </tr>
            <tr>
                <td class="original">XLarge</td>
                <td class="transformed">XL</td>
            </tr>
            <tr>
                <td class="original">XXLarge</td>
                <td class="transformed">XXL</td>
            </tr>
            <tr>
                <td class="original">XXXLarge</td>
                <td class="transformed">XXXL</td>
            </tr>
            <tr>
                <td class="original">Small/Medium</td>
                <td class="transformed">S/M</td>
            </tr>
            <tr>
                <td class="original">Medium/Large</td>
                <td class="transformed">M/L</td>
            </tr>
            <tr>
                <td class="original">Large/XLarge</td>
                <td class="transformed">L/XL</td>
            </tr>
        </table>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Simulate the WordPress localized data
        window.twcSizeTransform = {
            mapping: {
                'XSmall/Small': 'XS/S',
                'One Size': 'OS',
                'XSmall': 'XS',
                'Small': 'S',
                'Medium': 'M',
                'Large': 'L',
                'XLarge': 'XL',
                'XXLarge': 'XXL',
                'XXXLarge': 'XXXL',
                'Small/Medium': 'S/M',
                'Medium/Large': 'M/L',
                'Large/XLarge': 'L/XL'
            },
            attributes: ['select-size', 'size-selection']
        };
    </script>
    <script src="js/components/size-transformation.js"></script>
    
    <script>
        // Test the transformation function directly
        jQuery(document).ready(function($) {
            console.log('Testing size transformation...');
            
            // Test the transformSize function
            var testSizes = [
                'XSmall/Small', 'One Size', 'XSmall', 'Small', 'Medium', 
                'Large', 'XLarge', 'XXLarge', 'XXXLarge', 
                'Small/Medium', 'Medium/Large', 'Large/XLarge'
            ];
            
            testSizes.forEach(function(size) {
                var transformed = TWCSizeTransformation.transformSize(size);
                console.log(size + ' -> ' + transformed);
            });
            
            // Trigger manual transformation after a delay
            setTimeout(function() {
                console.log('Manual transformation triggered');
                TWCSizeTransformation.transformExistingSizes();
            }, 1000);
        });
    </script>
</body>
</html>